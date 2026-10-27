<?php
/**
 * Testes automatizados do recurso de IA e do plano B (não precisam de banco nem de internet).
 * Rodar no terminal:  php tests/testes_unitarios.php
 * (no Windows/XAMPP:  C:\xampp\php\php.exe tests\testes_unitarios.php)
 */
declare(strict_types=1);

if (!file_exists(__DIR__ . '/../config/config.php')) {
    copy(__DIR__ . '/../config/config.example.php', __DIR__ . '/../config/config.php');
}
require __DIR__ . '/../src/bootstrap.php';

$ok = 0;
$falhas = 0;
function teste(string $nome, callable $fn): void
{
    global $ok, $falhas;
    try {
        $fn();
        echo "  [OK]    $nome\n";
        $ok++;
    } catch (Throwable $ex) {
        echo "  [FALHA] $nome -> " . $ex->getMessage() . "\n";
        $falhas++;
    }
}
function garantir(bool $condicao, string $msg = 'condição falsa'): void
{
    if (!$condicao) throw new RuntimeException($msg);
}

// --- dados falsos (fixtures)
$hoje = date('Y-m-d');
$amanha = date('Y-m-d', strtotime('+1 day'));
$pontos = [
    ['id' => 1, 'nome' => 'Piscinas da Pajuçara', 'cidade' => 'Maceió', 'categoria' => 'praia', 'duracao_min' => 150, 'custo_estimado' => 50, 'nota_media' => '4.5'],
    ['id' => 2, 'nome' => 'Museu Théo Brandão', 'cidade' => 'Maceió', 'categoria' => 'cultura', 'duracao_min' => 90, 'custo_estimado' => 10, 'nota_media' => '4.0'],
    ['id' => 3, 'nome' => 'Orla', 'cidade' => 'Maceió', 'categoria' => 'praia', 'duracao_min' => 120, 'custo_estimado' => 0, 'nota_media' => null],
    ['id' => 4, 'nome' => 'Praia do Francês', 'cidade' => 'Marechal Deodoro', 'categoria' => 'praia', 'duracao_min' => 240, 'custo_estimado' => 30, 'nota_media' => '4.5'],
    ['id' => 5, 'nome' => 'Centro Histórico MD', 'cidade' => 'Marechal Deodoro', 'categoria' => 'historia', 'duracao_min' => 120, 'custo_estimado' => 0, 'nota_media' => null],
    ['id' => 6, 'nome' => 'Galés', 'cidade' => 'Maragogi', 'categoria' => 'praia', 'duracao_min' => 300, 'custo_estimado' => 120, 'nota_media' => '5.0'],
];
$eventos = [
    ['id' => 10, 'titulo' => 'Feira', 'cidade' => 'Maceió', 'local' => 'Pajuçara', 'inicio' => "$hoje 16:00:00", 'fim' => "$hoje 22:00:00", 'categoria' => 'feira', 'preco' => 0],
    ['id' => 11, 'titulo' => 'Luau', 'cidade' => 'Marechal Deodoro', 'local' => 'Francês', 'inicio' => "$amanha 19:00:00", 'fim' => "$amanha 23:00:00", 'categoria' => 'festa', 'preco' => 40],
];
$perfil = ['dias' => 2, 'data_inicio' => $hoje, 'cidade_base' => 'Maceió', 'interesses' => ['praia', 'cultura'],
           'companhia' => 'casal', 'ritmo' => 'moderado', 'orcamento' => 'medio'];

echo "\nAssistenteIA::interpretarResposta\n";

teste('CT-06 aceita JSON válido e converte para o formato interno', function () use ($perfil, $pontos, $eventos) {
    $json = '{"titulo":"Praias e cultura","resumo":"ok","dias":[{"dia":1,"atividades":[{"horario":"09:00","tipo":"ponto","id":1,"dica":"maré baixa"},{"horario":"16:30","tipo":"evento","id":10,"dica":"x"}]},{"dia":2,"atividades":[{"horario":"08:00","tipo":"ponto","id":4,"dica":"y"}]}]}';
    $r = AssistenteIA::interpretarResposta($json, $perfil, $pontos, $eventos);
    garantir($r['origem'] === 'ia');
    garantir(count($r['dias']) === 2);
    garantir($r['dias'][0]['itens'][0]['titulo'] === 'Piscinas da Pajuçara', 'título deve vir do banco');
    garantir($r['dias'][0]['itens'][1]['evento_id'] === 10);
});

teste('CT-07 descarta ID inventado pela IA (RN03)', function () use ($perfil, $pontos, $eventos) {
    $json = '{"titulo":"t","dias":[{"dia":1,"atividades":[{"horario":"09:00","tipo":"ponto","id":999},{"horario":"11:00","tipo":"ponto","id":2}]}]}';
    $r = AssistenteIA::interpretarResposta($json, $perfil, $pontos, $eventos);
    garantir(count($r['dias'][0]['itens']) === 1);
    garantir($r['dias'][0]['itens'][0]['ponto_id'] === 2);
});

teste('CT-07 descarta ponto repetido em outro dia', function () use ($perfil, $pontos, $eventos) {
    $json = '{"titulo":"t","dias":[{"dia":1,"atividades":[{"tipo":"ponto","id":1}]},{"dia":2,"atividades":[{"tipo":"ponto","id":1},{"tipo":"ponto","id":4}]}]}';
    $r = AssistenteIA::interpretarResposta($json, $perfil, $pontos, $eventos);
    garantir(count($r['dias'][1]['itens']) === 1 && $r['dias'][1]['itens'][0]['ponto_id'] === 4);
});

teste('CT-09 descarta evento fora da data do dia (RN04)', function () use ($perfil, $pontos, $eventos) {
    // evento 11 é amanhã (dia 2), mas a IA colocou no dia 1
    $json = '{"titulo":"t","dias":[{"dia":1,"atividades":[{"tipo":"ponto","id":1},{"tipo":"evento","id":11}]}]}';
    $r = AssistenteIA::interpretarResposta($json, $perfil, $pontos, $eventos);
    garantir(count($r['dias'][0]['itens']) === 1);
});

teste('CT-08 resposta que não é JSON gera exceção (aciona plano B)', function () use ($perfil, $pontos, $eventos) {
    try {
        AssistenteIA::interpretarResposta('Desculpe, não posso ajudar.', $perfil, $pontos, $eventos);
    } catch (RuntimeException) {
        return;
    }
    throw new RuntimeException('deveria lançar exceção');
});

teste('CT-08 JSON só com IDs inválidos gera exceção (aciona plano B)', function () use ($perfil, $pontos, $eventos) {
    try {
        AssistenteIA::interpretarResposta('{"dias":[{"dia":1,"atividades":[{"tipo":"ponto","id":77}]}]}', $perfil, $pontos, $eventos);
    } catch (RuntimeException) {
        return;
    }
    throw new RuntimeException('deveria lançar exceção');
});

teste('aceita JSON embrulhado em ```json', function () use ($perfil, $pontos, $eventos) {
    $r = AssistenteIA::interpretarResposta("```json\n{\"dias\":[{\"dia\":1,\"atividades\":[{\"tipo\":\"ponto\",\"id\":2}]}]}\n```", $perfil, $pontos, $eventos);
    garantir($r['dias'][0]['itens'][0]['ponto_id'] === 2);
});

teste('CT-08 sem chave da API lança exceção antes de chamar a internet', function () use ($perfil, $pontos, $eventos) {
    try {
        (new AssistenteIA(''))->montarRoteiro($perfil, $pontos, $eventos);
    } catch (RuntimeException $ex) {
        garantir(str_contains($ex->getMessage(), 'Chave'));
        return;
    }
    throw new RuntimeException('deveria lançar exceção');
});

teste('prompt contém o perfil e os IDs do banco', function () use ($perfil, $pontos, $eventos) {
    $p = (new AssistenteIA('x'))->montarPrompt($perfil, $pontos, $eventos);
    garantir(str_contains($p, '"id":4') && str_contains($p, 'Maceió') && str_contains($p, 'moderado') && str_contains($p, '"praia"'));
});

echo "\nPlanejadorLocal (plano B)\n";

teste('CT-08 gera um dia para cada dia da viagem', function () use ($perfil, $pontos, $eventos) {
    $r = PlanejadorLocal::montar($perfil, $pontos, $eventos);
    garantir($r['origem'] === 'padrao' && count($r['dias']) === 2);
});

teste('não repete pontos entre os dias', function () use ($pontos, $eventos, $hoje) {
    $p = ['dias' => 5, 'data_inicio' => $hoje, 'cidade_base' => 'Maceió', 'interesses' => ['praia'], 'companhia' => 'amigos', 'ritmo' => 'intenso', 'orcamento' => 'livre'];
    $ids = [];
    foreach (PlanejadorLocal::montar($p, $pontos, $eventos)['dias'] as $d) {
        foreach ($d['itens'] as $i) if ($i['ponto_id']) $ids[] = $i['ponto_id'];
    }
    garantir(count($ids) === count(array_unique($ids)));
});

teste('RN05 ritmo tranquilo não passa de ~4h por dia (exceto 1º item longo)', function () use ($pontos, $hoje) {
    $p = ['dias' => 1, 'data_inicio' => $hoje, 'cidade_base' => 'Maceió', 'interesses' => ['praia', 'cultura'], 'companhia' => 'casal', 'ritmo' => 'tranquilo', 'orcamento' => 'medio'];
    $total = 0;
    foreach (PlanejadorLocal::montar($p, $pontos, [])['dias'][0]['itens'] as $i) {
        $total += (int) array_column($pontos, null, 'id')[$i['ponto_id']]['duracao_min'];
    }
    garantir($total <= 240, "total $total min");
});

teste('RN04 inclui evento só no dia em que acontece', function () use ($perfil, $pontos, $eventos) {
    $r = PlanejadorLocal::montar($perfil, $pontos, $eventos);
    $dia1 = array_filter(array_column($r['dias'][0]['itens'], 'evento_id'));
    garantir(in_array(10, $dia1, true) && !in_array(11, $dia1, true));
});

teste('cidade-base sem pontos cadastrados não deixa o 1º dia vazio', function () use ($pontos, $hoje) {
    $p = ['dias' => 1, 'data_inicio' => $hoje, 'cidade_base' => 'Arapiraca', 'interesses' => ['praia'], 'companhia' => 'casal', 'ritmo' => 'moderado', 'orcamento' => 'medio'];
    garantir(count(PlanejadorLocal::montar($p, $pontos, [])['dias'][0]['itens']) > 0);
});

echo "\nDados da planilha (período, maré, tags)\n";

$pontosPlanilha = [
    ['id' => 21, 'nome' => 'Mirante', 'cidade' => 'Maceió', 'categoria' => 'natureza', 'duracao_min' => 30, 'custo_estimado' => 0, 'nota_media' => null, 'melhor_periodo' => 'fim_de_tarde', 'tags' => 'fotografia'],
    ['id' => 22, 'nome' => 'Piscinas', 'cidade' => 'Maceió', 'categoria' => 'praia', 'duracao_min' => 120, 'custo_estimado' => 50, 'nota_media' => null, 'melhor_periodo' => 'manha', 'depende_mare' => 1, 'tags' => 'praia;familia'],
    ['id' => 23, 'nome' => 'Polo gastronômico', 'cidade' => 'Maceió', 'categoria' => 'gastronomia', 'duracao_min' => 90, 'custo_estimado' => 60, 'nota_media' => null, 'melhor_periodo' => 'noite', 'tags' => 'gastronomia;vida_noturna'],
];

teste('plano B ordena o dia pelo melhor período (manhã → fim de tarde → noite)', function () use ($pontosPlanilha, $hoje) {
    $p = ['dias' => 1, 'data_inicio' => $hoje, 'cidade_base' => 'Maceió', 'interesses' => ['praia', 'natureza', 'gastronomia'], 'companhia' => 'casal', 'ritmo' => 'intenso', 'orcamento' => 'livre'];
    $itens = PlanejadorLocal::montar($p, $pontosPlanilha, [])['dias'][0]['itens'];
    garantir(array_column($itens, 'ponto_id') === [22, 21, 23], 'ordem ' . json_encode(array_column($itens, 'ponto_id')));
    garantir($itens[1]['horario'] >= '16:00' && $itens[2]['horario'] >= '19:00', 'horários ' . json_encode(array_column($itens, 'horario')));
});

teste('plano B avisa da maré baixa na dica', function () use ($pontosPlanilha, $hoje) {
    $p = ['dias' => 1, 'data_inicio' => $hoje, 'cidade_base' => 'Maceió', 'interesses' => ['praia'], 'companhia' => 'casal', 'ritmo' => 'intenso', 'orcamento' => 'livre'];
    $itens = array_column(PlanejadorLocal::montar($p, $pontosPlanilha, [])['dias'][0]['itens'], null, 'ponto_id');
    garantir(str_contains($itens[22]['dica'], 'maré'));
});

teste('tags contam como interesse quando a categoria não bate', function () use ($hoje) {
    $pts = [
        ['id' => 31, 'nome' => 'A', 'cidade' => 'Maceió', 'categoria' => 'natureza', 'duracao_min' => 200, 'custo_estimado' => 0, 'nota_media' => null, 'tags' => 'natureza'],
        ['id' => 32, 'nome' => 'B', 'cidade' => 'Maceió', 'categoria' => 'natureza', 'duracao_min' => 200, 'custo_estimado' => 0, 'nota_media' => null, 'tags' => 'natureza;praia'],
    ];
    $p = ['dias' => 1, 'data_inicio' => $hoje, 'cidade_base' => 'Maceió', 'interesses' => ['praia'], 'companhia' => 'casal', 'ritmo' => 'tranquilo', 'orcamento' => 'livre'];
    garantir(PlanejadorLocal::montar($p, $pts, [])['dias'][0]['itens'][0]['ponto_id'] === 32);
});

teste('prompt da IA leva tags, período e maré', function () use ($perfil, $pontosPlanilha) {
    $p = (new AssistenteIA('x'))->montarPrompt($perfil, $pontosPlanilha, []);
    garantir(str_contains($p, '"mare_baixa":true') && str_contains($p, '"melhor_periodo":"noite"') && str_contains($p, '"vida_noturna"'));
});

teste('normaliza tags digitadas pelo guia', function () {
    garantir(PontoTuristico::normalizarTags(' Praia; Família , praia;Vida noturna ') === 'praia;familia;vida_noturna');
});

teste('preço sem valor e sem faixa aparece como "a confirmar"', function () {
    garantir(preco_ponto(['custo_estimado' => 0, 'faixa_preco' => null]) === 'Preço a confirmar');
    garantir(preco_ponto(['custo_estimado' => 0, 'faixa_preco' => 'gratuito']) === 'Grátis');
});

echo "\nRoteiro::validarPerfil\n";

teste('CT-05 recusa 0 dias, 8 dias e nenhum interesse', function () use ($perfil) {
    [, $e1] = Roteiro::validarPerfil(array_merge($perfil, ['dias' => 0]));
    [, $e2] = Roteiro::validarPerfil(array_merge($perfil, ['dias' => 8]));
    [, $e3] = Roteiro::validarPerfil(array_merge($perfil, ['interesses' => []]));
    garantir($e1 && $e2 && $e3);
});

teste('CT-05 recusa data no passado e aceita perfil válido', function () use ($perfil) {
    [, $e1] = Roteiro::validarPerfil(array_merge($perfil, ['data_inicio' => '2020-01-01']));
    [, $e2] = Roteiro::validarPerfil($perfil);
    garantir($e1 && !$e2);
});

teste('ignora interesse que não existe (ex.: injeção no formulário)', function () use ($perfil) {
    [$p] = Roteiro::validarPerfil(array_merge($perfil, ['interesses' => ['praia', '<script>']]));
    garantir($p['interesses'] === ['praia']);
});

echo "\nResultado: $ok passou, $falhas falhou.\n";
exit($falhas ? 1 : 0);
