<?php
declare(strict_types=1);

/**
 * Recurso de IA do Rota Alagoas.
 *
 * ENTRADA: perfil do visitante + pontos turísticos e eventos do banco.
 * SAÍDA:   roteiro dia a dia (array) contendo só IDs que existem no banco.
 * FALHA:   lança RuntimeException — quem chama aciona o PlanejadorLocal (plano B).
 */
class AssistenteIA
{
    private const URL = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';

    public function __construct(
        private string $chave,
        private string $modelo = 'gemini-3.8-flash',
        private int $timeout = 30,
        private bool $verificarSsl = true,
        private array $modelosReserva = [],
    ) {}

    public static function daConfig(): self
    {
        $chave = (string) (config('gemini.chave') ?: getenv('GEMINI_API_KEY') ?: '');
        return new self(
            $chave,
            (string) config('gemini.modelo', 'gemini-3.8-flash'),
            (int) config('gemini.timeout', 30),
            (bool) config('gemini.verificar_ssl', true),
            (array) config('gemini.modelos_reserva', []),
        );
    }

    public function montarRoteiro(array $perfil, array $pontos, array $eventos): array
    {
        if ($this->chave === '') {
            throw new RuntimeException('Chave da API Gemini não configurada.');
        }
        $texto = $this->chamarGemini($this->montarPrompt($perfil, $pontos, $eventos));
        return self::interpretarResposta($texto, $perfil, $pontos, $eventos);
    }

    public function montarPrompt(array $perfil, array $pontos, array $eventos): string
    {
        $listaPontos = array_map(fn($p) => array_filter([
            'id' => (int) $p['id'],
            'nome' => $p['nome'],
            'cidade' => $p['cidade'],
            'bairro' => $p['bairro'] ?? null,
            'categoria' => $p['categoria'],
            'tags' => tags_ponto($p) ?: null,
            'duracao_min' => (int) $p['duracao_min'],
            'custo' => (float) $p['custo_estimado'],
            'melhor_periodo' => $p['melhor_periodo'] ?? null,
            'mare_baixa' => !empty($p['depende_mare']) ? true : null,
            'nota' => $p['nota_media'] !== null ? (float) $p['nota_media'] : null,
        ], fn($v) => $v !== null), $pontos);

        $listaEventos = array_map(fn($e) => [
            'id' => (int) $e['id'],
            'titulo' => $e['titulo'],
            'cidade' => $e['cidade'],
            'local' => $e['local'],
            'inicio' => $e['inicio'],
            'fim' => $e['fim'],
            'categoria' => $e['categoria'],
            'preco' => (float) $e['preco'],
        ], $eventos);

        $horas = PlanejadorLocal::MINUTOS_POR_RITMO[$perfil['ritmo']] / 60;
        $fimViagem = date('Y-m-d', strtotime($perfil['data_inicio'] . ' +' . ($perfil['dias'] - 1) . ' days'));

        $json = fn($v) => json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return <<<PROMPT
Você é um guia de turismo especialista em Alagoas, Brasil. Monte um roteiro de viagem em português do Brasil.

PERFIL DO VISITANTE
- Período: {$perfil['dias']} dia(s), de {$perfil['data_inicio']} a {$fimViagem}
- Hospedado em: {$perfil['cidade_base']}
- Interesses: {$json($perfil['interesses'])}
- Companhia: {$perfil['companhia']}
- Ritmo: {$perfil['ritmo']} (cerca de {$horas} horas de atividades por dia)
- Orçamento: {$perfil['orcamento']}

PONTOS TURÍSTICOS DISPONÍVEIS (use somente estes, pelo id):
{$json($listaPontos)}

EVENTOS NO PERÍODO (use somente estes, pelo id, e só no dia em que acontecem):
{$json($listaEventos)}

REGRAS
1. Use APENAS ids das listas acima. Não invente lugares.
2. Não repita o mesmo ponto em dias diferentes.
3. Agrupe pontos da mesma cidade no mesmo dia e considere o tempo de deslocamento a partir de {$perfil['cidade_base']} (Maragogi, Penedo, Piranhas e Piaçabuçu ficam a 2–4 h de carro de Maceió).
4. Respeite a soma de duracao_min com o ritmo do visitante. Priorize os interesses e as notas mais altas.
5. Orçamento "economico": prefira custo baixo. Família com crianças: prefira atividades calmas.
6. Inclua um evento quando combinar com o perfil, no horário de início dele.
7. A "dica" deve ser curta (até 140 caracteres) e prática.
8. Respeite "melhor_periodo": manha no início do dia, tarde depois do almoço, fim_de_tarde por volta das 16h–17h (mirantes, pôr do sol) e noite a partir das 19h.
9. Pontos com "mare_baixa": true dependem da maré baixa; coloque pela manhã e diga na dica para conferir a tábua de marés.
10. Use as "tags" para combinar com os interesses e a companhia (ex.: familia para família com crianças, tranquilidade para ritmo tranquilo, vida_noturna para amigos).

Responda SOMENTE com JSON válido neste formato:
{"titulo": "string", "resumo": "string de até 300 caracteres",
 "dias": [{"dia": 1, "atividades": [{"horario": "HH:MM", "tipo": "ponto" ou "evento", "id": 0, "dica": "string"}]}]}
PROMPT;
    }

    private function chamarGemini(string $prompt): string
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('Extensão cURL do PHP desativada.');
        }
        $corpo = [
            'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
            'generationConfig' => ['responseMimeType' => 'application/json', 'temperature' => 0.6],
        ];
        // Erros temporários do Google (sobrecarga/limite): tenta de novo e,
        // se o modelo principal continuar fora, passa para os modelos de reserva.
        $modelos = array_values(array_unique(array_filter(
            array_merge([$this->modelo], $this->modelosReserva), 'is_string')));
        $ultimoErro = null;
        foreach ($modelos as $modelo) {
            for ($i = 1; $i <= 2; $i++) {
                try {
                    return $this->enviarRequisicao($corpo, $modelo);
                } catch (RuntimeException $e) {
                    $ultimoErro = $e;
                    $codigo = $e->getCode();
                    if (in_array($codigo, [429, 500, 503], true) && $i < 2) {
                        sleep(2);          // tenta o mesmo modelo mais uma vez
                        continue;
                    }
                    if (in_array($codigo, [404, 429, 500, 503, 504], true)) {
                        continue 2;        // vai para o próximo modelo
                    }
                    throw $e;              // chave inválida etc.: não adianta insistir
                }
            }
        }
        throw $ultimoErro ?? new RuntimeException('Nenhum modelo de IA configurado.');
    }

    private function enviarRequisicao(array $corpo, string $modelo): string
    {
        $ch = curl_init(sprintf(self::URL, rawurlencode($modelo)));
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'x-goog-api-key: ' . $this->chave],
            CURLOPT_POSTFIELDS     => json_encode($corpo, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => $this->verificarSsl,
            CURLOPT_SSL_VERIFYHOST => $this->verificarSsl ? 2 : 0,
        ]);
        $resposta = curl_exec($ch);
        $erroCurl = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($resposta === false) {
            // 504 = sem resposta a tempo (modelo travado/sobrecarregado): tenta o próximo modelo
            throw new RuntimeException("Falha de conexão com a IA ($modelo): " . $erroCurl, 504);
        }
        $dados = json_decode((string) $resposta, true);
        if ($status !== 200) {
            $msg = $dados['error']['message'] ?? substr((string) $resposta, 0, 200);
            throw new RuntimeException("A IA ($modelo) respondeu HTTP $status: $msg", $status);
        }
        $texto = $dados['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!is_string($texto) || $texto === '') {
            throw new RuntimeException('A IA não devolveu conteúdo.');
        }
        return $texto;
    }

    /**
     * Valida o JSON da IA e converte para o formato interno.
     * Descarta IDs inexistentes, pontos repetidos e eventos fora da data (RN03, RN04).
     */
    public static function interpretarResposta(string $texto, array $perfil, array $pontos, array $eventos): array
    {
        // Às vezes o modelo embrulha o JSON em ```json ... ```
        $texto = trim(preg_replace('/^```(?:json)?\s*|\s*```$/', '', trim($texto)));
        $json = json_decode($texto, true);
        if (!is_array($json) || !isset($json['dias']) || !is_array($json['dias'])) {
            throw new RuntimeException('Resposta da IA fora do formato esperado.');
        }

        $mapaPontos = array_column($pontos, null, 'id');
        $mapaEventos = array_column($eventos, null, 'id');
        $usados = [];
        $dias = [];
        $totalItens = 0;

        foreach ($json['dias'] as $d) {
            $num = (int) ($d['dia'] ?? 0);
            if ($num < 1 || $num > $perfil['dias'] || isset($dias[$num])) {
                continue;
            }
            $dataDia = date('Y-m-d', strtotime($perfil['data_inicio'] . ' +' . ($num - 1) . ' days'));
            $itens = [];
            foreach (($d['atividades'] ?? []) as $a) {
                $id = (int) ($a['id'] ?? 0);
                $tipo = $a['tipo'] ?? '';
                $horario = (isset($a['horario']) && preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', (string) $a['horario'])) ? $a['horario'] : null;
                $dica = mb_substr(trim((string) ($a['dica'] ?? '')), 0, 300) ?: null;

                if ($tipo === 'ponto' && isset($mapaPontos[$id]) && !isset($usados[$id])) {
                    $usados[$id] = true;
                    $itens[] = ['horario' => $horario, 'ponto_id' => $id, 'evento_id' => null, 'titulo' => $mapaPontos[$id]['nome'], 'dica' => $dica];
                } elseif ($tipo === 'evento' && isset($mapaEventos[$id])) {
                    $ev = $mapaEventos[$id];
                    if (substr($ev['inicio'], 0, 10) <= $dataDia && substr($ev['fim'], 0, 10) >= $dataDia) {
                        $itens[] = ['horario' => $horario ?? substr($ev['inicio'], 11, 5), 'ponto_id' => null, 'evento_id' => $id, 'titulo' => $ev['titulo'], 'dica' => $dica];
                    }
                }
            }
            usort($itens, fn($x, $y) => strcmp($x['horario'] ?? '99', $y['horario'] ?? '99'));
            $dias[$num] = ['dia' => $num, 'itens' => $itens];
            $totalItens += count($itens);
        }

        if ($totalItens === 0) {
            throw new RuntimeException('A IA não sugeriu nenhum item válido.');
        }
        ksort($dias);

        return [
            'titulo' => mb_substr(trim((string) ($json['titulo'] ?? '')), 0, 150) ?: 'Roteiro de ' . $perfil['dias'] . ' dia(s) em Alagoas',
            'resumo' => mb_substr(trim((string) ($json['resumo'] ?? '')), 0, 600),
            'origem' => 'ia',
            'dias'   => array_values($dias),
        ];
    }
}
