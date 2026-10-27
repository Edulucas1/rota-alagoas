<?php
declare(strict_types=1);

class Roteiro
{
    /** Valida o formulário e devolve [perfil, erros]. */
    public static function validarPerfil(array $d): array
    {
        $erros = [];
        $dias = (int) ($d['dias'] ?? 0);
        $data = (string) ($d['data_inicio'] ?? '');
        $interesses = array_values(array_intersect((array) ($d['interesses'] ?? []), array_keys(CATEGORIAS_PONTO)));

        if ($dias < 1 || $dias > 7) $erros[] = 'Escolha de 1 a 7 dias.';
        $ts = strtotime($data);
        if (!$ts || date('Y-m-d', $ts) !== $data) {
            $erros[] = 'Informe a data de chegada.';
        } elseif ($data < date('Y-m-d')) {
            $erros[] = 'A data de chegada não pode estar no passado.';
        }
        if (!$interesses) $erros[] = 'Marque pelo menos um interesse.';
        if (trim($d['cidade_base'] ?? '') === '') $erros[] = 'Informe onde vai se hospedar.';
        if (!array_key_exists($d['companhia'] ?? '', COMPANHIAS)) $erros[] = 'Escolha a companhia.';
        if (!array_key_exists($d['ritmo'] ?? '', RITMOS)) $erros[] = 'Escolha o ritmo.';
        if (!array_key_exists($d['orcamento'] ?? '', ORCAMENTOS)) $erros[] = 'Escolha o orçamento.';

        $perfil = [
            'dias' => $dias, 'data_inicio' => $data, 'cidade_base' => trim($d['cidade_base'] ?? ''),
            'interesses' => $interesses, 'companhia' => $d['companhia'] ?? '',
            'ritmo' => $d['ritmo'] ?? '', 'orcamento' => $d['orcamento'] ?? '',
        ];
        return [$perfil, $erros];
    }

    /**
     * Gera o roteiro: tenta a IA e, se falhar, usa o plano B.
     * Devolve o roteiro; em caso de plano B inclui 'aviso' e 'erro_ia'.
     */
    public static function gerar(array $perfil, ?AssistenteIA $ia = null): array
    {
        $pontos = PontoTuristico::listar();
        $fim = date('Y-m-d', strtotime($perfil['data_inicio'] . ' +' . ($perfil['dias'] - 1) . ' days'));
        $eventos = Evento::noPeriodo($perfil['data_inicio'], $fim);

        try {
            return ($ia ?? AssistenteIA::daConfig())->montarRoteiro($perfil, $pontos, $eventos);
        } catch (Throwable $ex) {
            error_log('[Rota Alagoas] IA falhou, usando plano B: ' . $ex->getMessage());
            $roteiro = PlanejadorLocal::montar($perfil, $pontos, $eventos);
            $roteiro['aviso'] = 'Roteiro montado sem IA: o assistente está indisponível no momento, então usamos as melhores avaliações e seus interesses.';
            $roteiro['erro_ia'] = $ex->getMessage();
            return $roteiro;
        }
    }

    /** Grava roteiro e itens numa transação. */
    public static function salvar(int $usuarioId, array $perfil, array $roteiro): int
    {
        $pdo = Database::conexao();
        $pdo->beginTransaction();
        try {
            Database::executar(
                'INSERT INTO roteiros (usuario_id, titulo, resumo, data_inicio, dias, cidade_base, interesses, companhia, ritmo, orcamento, origem)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [$usuarioId, $roteiro['titulo'], $roteiro['resumo'] ?: null, $perfil['data_inicio'], $perfil['dias'],
                 $perfil['cidade_base'], implode(',', $perfil['interesses']), $perfil['companhia'],
                 $perfil['ritmo'], $perfil['orcamento'], $roteiro['origem']]
            );
            $roteiroId = (int) $pdo->lastInsertId();
            $st = $pdo->prepare('INSERT INTO roteiro_itens (roteiro_id, dia, ordem, horario, ponto_id, evento_id, titulo, dica) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            foreach ($roteiro['dias'] as $dia) {
                foreach ($dia['itens'] as $ordem => $item) {
                    $st->execute([$roteiroId, $dia['dia'], $ordem + 1, $item['horario'], $item['ponto_id'], $item['evento_id'], $item['titulo'], $item['dica']]);
                }
            }
            $pdo->commit();
            return $roteiroId;
        } catch (Throwable $ex) {
            $pdo->rollBack();
            throw $ex;
        }
    }

    public static function listarDoUsuario(int $usuarioId): array
    {
        return Database::executar(
            'SELECT r.*, COUNT(i.id) AS total_itens FROM roteiros r LEFT JOIN roteiro_itens i ON i.roteiro_id = r.id
             WHERE r.usuario_id = ? GROUP BY r.id ORDER BY r.criado_em DESC',
            [$usuarioId]
        )->fetchAll();
    }

    /** Busca o roteiro só se pertencer ao usuário (HU02). */
    public static function buscarComItens(int $id, int $usuarioId): ?array
    {
        $r = Database::executar('SELECT * FROM roteiros WHERE id = ? AND usuario_id = ?', [$id, $usuarioId])->fetch();
        if (!$r) return null;

        $itens = Database::executar(
            'SELECT i.*, p.cidade AS ponto_cidade, p.categoria AS ponto_categoria, p.duracao_min, p.custo_estimado,
                    p.latitude, p.longitude, p.depende_mare,
                    e.local AS evento_local, e.cidade AS evento_cidade, e.preco AS evento_preco
             FROM roteiro_itens i
             LEFT JOIN pontos_turisticos p ON p.id = i.ponto_id
             LEFT JOIN eventos e ON e.id = i.evento_id
             WHERE i.roteiro_id = ? ORDER BY i.dia, i.ordem',
            [$id]
        )->fetchAll();

        $r['dias_itens'] = [];
        for ($d = 1; $d <= (int) $r['dias']; $d++) {
            $r['dias_itens'][$d] = [];
        }
        foreach ($itens as $i) {
            $r['dias_itens'][(int) $i['dia']][] = $i;
        }
        return $r;
    }

    /**
     * Pontos do roteiro que têm coordenadas, numerados dentro de cada dia, para o mapa.
     * Devolve [marcadores, nomes dos pontos sem localização].
     */
    public static function marcadoresDoMapa(array $r): array
    {
        $marcadores = [];
        $semLocal = [];
        foreach ($r['dias_itens'] as $dia => $itens) {
            $n = 0;
            foreach ($itens as $i) {
                if (!$i['ponto_id']) continue;               // eventos ainda não têm coordenadas
                $n++;
                if ($i['latitude'] === null || $i['longitude'] === null) {
                    $semLocal[] = $i['titulo'];
                    continue;
                }
                $marcadores[] = [
                    'dia' => (int) $dia, 'n' => $n, 'id' => (int) $i['ponto_id'], 'titulo' => $i['titulo'],
                    'horario' => $i['horario'] ? substr($i['horario'], 0, 5) : '',
                    'lat' => (float) $i['latitude'], 'lng' => (float) $i['longitude'],
                ];
            }
        }
        return [$marcadores, $semLocal];
    }

    /** Centro aproximado das cidades (para a previsão quando o dia não tem ponto com coordenadas). */
    public const CIDADES = [
        'Maceió' => [-9.6658, -35.7353, true], 'Marechal Deodoro' => [-9.7100, -35.8950, true],
        'Barra de São Miguel' => [-9.8380, -35.8920, true], 'Roteiro' => [-9.8340, -35.9790, true],
        'Maragogi' => [-9.0122, -35.2225, true], 'São Miguel dos Milagres' => [-9.2650, -35.3760, true],
        'Porto de Pedras' => [-9.1600, -35.2950, true], 'Barra de Santo Antônio' => [-9.4040, -35.5070, true],
        'Piaçabuçu' => [-10.4060, -36.4340, true], 'Penedo' => [-10.2900, -36.5860, false],
        'Piranhas' => [-9.6240, -37.7570, false], 'União dos Palmares' => [-9.1630, -36.0310, false],
    ];

    /**
     * Onde o visitante está em cada dia: coordenadas do 1º ponto com localização, senão o centro
     * da cidade do dia. "litoral" diz se faz sentido mostrar maré.
     */
    public static function localDosDias(array $r): array
    {
        $locais = [];
        foreach ($r['dias_itens'] as $dia => $itens) {
            // cidade do 1º item do dia (dia livre = cidade onde está hospedado)
            $cidade = $itens ? ($itens[0]['ponto_cidade'] ?? $itens[0]['evento_cidade'] ?? $r['cidade_base']) : $r['cidade_base'];
            $coord = null;
            foreach ($itens as $i) {
                if ($i['ponto_id'] && $i['latitude'] !== null && $coord === null) {
                    $coord = [(float) $i['latitude'], (float) $i['longitude']];
                    $cidade = $i['ponto_cidade'];
                }
            }
            $ref = self::CIDADES[$cidade] ?? null;
            if (!$coord && $ref) $coord = [$ref[0], $ref[1]];
            if (!$coord) continue;   // cidade desconhecida e sem coordenadas: sem previsão
            $locais[$dia] = ['lat' => $coord[0], 'lng' => $coord[1], 'cidade' => $cidade, 'litoral' => $ref ? $ref[2] : true];
        }
        return $locais;
    }

    public static function excluir(int $id, int $usuarioId): bool
    {
        return Database::executar('DELETE FROM roteiros WHERE id = ? AND usuario_id = ?', [$id, $usuarioId])->rowCount() > 0;
    }
}
