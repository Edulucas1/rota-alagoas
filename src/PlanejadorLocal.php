<?php
declare(strict_types=1);

/**
 * PLANO B — monta o roteiro sem IA (RNF02).
 * Pontua cada ponto pelos interesses, nota e orçamento, agrupa por cidade
 * e distribui pelos dias respeitando o ritmo (RN05).
 */
class PlanejadorLocal
{
    public const MINUTOS_POR_RITMO = ['tranquilo' => 240, 'moderado' => 360, 'intenso' => 480];
    private const DESLOCAMENTO_MIN = 30;   // entre atividades na mesma cidade
    private const VIAGEM_MIN = 90;         // ida a outra cidade (média)
    /** Ordem no dia e horário mínimo de cada "melhor período" (dados da planilha). */
    private const PERIODO = [
        'manha' => [0, '08:30'], 'qualquer' => [1, '08:30'], 'tarde' => [2, '13:30'],
        'fim_de_tarde' => [3, '16:00'], 'noite' => [4, '19:00'],
    ];

    public static function montar(array $perfil, array $pontos, array $eventos): array
    {
        $limite = self::MINUTOS_POR_RITMO[$perfil['ritmo']] ?? 360;

        // 1. Pontuação de cada ponto
        $candidatos = [];
        foreach ($pontos as $p) {
            $p['score'] = self::pontuar($p, $perfil);
            $candidatos[(int) $p['id']] = $p;
        }
        uasort($candidatos, fn($a, $b) => $b['score'] <=> $a['score']);

        $dias = [];
        for ($d = 1; $d <= $perfil['dias']; $d++) {
            $dataDia = date('Y-m-d', strtotime($perfil['data_inicio'] . ' +' . ($d - 1) . ' days'));
            $itens = [];

            // 2. Cidade do dia = cidade do melhor ponto ainda não usado
            //    (1º dia sempre na cidade-base, para descansar da chegada)
            $cidadeDia = $perfil['cidade_base'];
            if ($d > 1 && $candidatos) {
                $cidadeDia = reset($candidatos)['cidade'];
            }

            // 3. Preenche com pontos da cidade do dia até o limite do ritmo
            $itens = self::preencherDia($candidatos, $cidadeDia, $perfil['cidade_base'], $dataDia, $limite);
            if (!$itens && $candidatos) {
                // Cidade-base sem pontos cadastrados: vai para a cidade do melhor ponto
                $cidadeDia = reset($candidatos)['cidade'];
                $itens = self::preencherDia($candidatos, $cidadeDia, $perfil['cidade_base'], $dataDia, $limite);
            }

            // 4. Evento do dia na mesma cidade em que o visitante está, se houver
            foreach ($eventos as $ev) {
                $naData = substr($ev['inicio'], 0, 10) <= $dataDia && substr($ev['fim'], 0, 10) >= $dataDia;
                if ($naData && $ev['cidade'] === $cidadeDia) {
                    $itens[] = [
                        'horario' => substr($ev['inicio'], 11, 5), 'ponto_id' => null, 'evento_id' => (int) $ev['id'],
                        'titulo' => $ev['titulo'], 'dica' => 'Evento em ' . $ev['local'] . ' — ' . dinheiro((float) $ev['preco']) . '.',
                    ];
                    break;
                }
            }

            usort($itens, fn($x, $y) => strcmp($x['horario'], $y['horario']));
            $dias[] = ['dia' => $d, 'itens' => $itens];
        }

        $nomes = array_map(fn($i) => CATEGORIAS_PONTO[$i] ?? $i, $perfil['interesses']);
        return [
            'titulo' => 'Roteiro de ' . $perfil['dias'] . ' dia(s) em Alagoas',
            'resumo' => 'Roteiro montado automaticamente com base nos seus interesses (' . implode(', ', $nomes)
                . ') e nas melhores avaliações dos visitantes.',
            'origem' => 'padrao',
            'dias'   => $dias,
        ];
    }

    /** Escolhe pontos da cidade do dia; remove de $candidatos os que usou. */
    private static function preencherDia(array &$candidatos, string $cidadeDia, string $cidadeBase, string $dataDia, int $limite): array
    {
        // 1. Escolhe os pontos que cabem no ritmo do dia
        $escolhidos = [];
        $minutos = $cidadeDia === $cidadeBase ? 0 : self::VIAGEM_MIN;
        $inicio = $minutos;
        foreach ($candidatos as $id => $p) {
            if ($p['cidade'] !== $cidadeDia) continue;
            $custo = (int) $p['duracao_min'] + ($escolhidos ? self::DESLOCAMENTO_MIN : 0);
            if ($escolhidos && $minutos + $custo > $limite) continue;
            $escolhidos[$id] = $p;
            $minutos += $custo;
            unset($candidatos[$id]);
            if ($minutos >= $limite) break;
        }

        // 2. Ordena pelo melhor período (manhã → noite) e calcula os horários
        uasort($escolhidos, fn($a, $b) => self::periodo($a)[0] <=> self::periodo($b)[0]);
        $itens = [];
        $relogio = strtotime($dataDia . ' 08:30') + $inicio * 60;
        foreach ($escolhidos as $id => $p) {
            if ($itens) $relogio += self::DESLOCAMENTO_MIN * 60;
            $relogio = max($relogio, strtotime($dataDia . ' ' . self::periodo($p)[1]));
            $itens[] = [
                'horario' => date('H:i', $relogio), 'ponto_id' => $id, 'evento_id' => null,
                'titulo' => $p['nome'], 'dica' => self::dica($p),
            ];
            $relogio += (int) $p['duracao_min'] * 60;
        }
        return $itens;
    }

    private static function periodo(array $p): array
    {
        return self::PERIODO[$p['melhor_periodo'] ?? 'qualquer'] ?? self::PERIODO['qualquer'];
    }

    private static function pontuar(array $p, array $perfil): float
    {
        $nota = $p['nota_media'] !== null ? (float) $p['nota_media'] : 3.5;
        $score = $nota;
        $tags = tags_ponto($p);
        if (in_array($p['categoria'], $perfil['interesses'], true)) {
            $score += 3;
        } elseif (array_intersect($tags, $perfil['interesses'])) {
            $score += 1.5;   // a categoria não bate, mas as tags da planilha sim
        }
        if ($perfil['companhia'] === 'familia' && in_array('familia', $tags, true)) $score += 1;
        if ($perfil['companhia'] === 'amigos' && in_array('vida_noturna', $tags, true)) $score += 0.5;
        if ($perfil['ritmo'] === 'tranquilo' && in_array('tranquilidade', $tags, true)) $score += 0.5;
        $custo = (float) $p['custo_estimado'];
        if ($perfil['orcamento'] === 'economico' && $custo > 60) $score -= 2;
        if ($perfil['orcamento'] === 'medio' && $custo > 120) $score -= 1;
        if ($perfil['companhia'] === 'familia' && $p['categoria'] === 'aventura') $score -= 1;
        if ($perfil['ritmo'] === 'tranquilo' && (int) $p['duracao_min'] > 240) $score -= 1;
        if ($p['cidade'] === $perfil['cidade_base']) $score += 0.5;
        return $score;
    }

    private static function dica(array $p): string
    {
        $dica = 'Reserve ' . duracao((int) $p['duracao_min']) . '. Custo estimado: ' . preco_ponto($p) . '.';
        if (!empty($p['depende_mare'])) {
            $dica .= ' Depende da maré baixa: confira a tábua de marés.';
        } elseif (($p['melhor_periodo'] ?? '') === 'fim_de_tarde') {
            $dica .= ' Melhor no fim de tarde.';
        }
        return $dica;
    }
}
