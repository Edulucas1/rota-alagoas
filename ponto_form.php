<?php
require __DIR__ . '/src/bootstrap.php';
$u = exigir_login('guia');

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$dados = ['nome' => '', 'cidade' => '', 'bairro' => '', 'categoria' => '', 'descricao' => '', 'endereco' => '', 'duracao_min' => 120,
          'custo_estimado' => 0, 'faixa_preco' => '', 'depende_mare' => 0, 'melhor_periodo' => 'qualquer', 'tags' => '',
          'latitude' => '', 'longitude' => ''];

if ($id !== null) {
    $p = PontoTuristico::buscar($id);
    if (!$p || (int) $p['criado_por'] !== $u['id']) {
        flash('erro', 'Você só pode editar pontos que cadastrou.');
        redirecionar('painel.php');
    }
    $dados = array_map(fn($v) => $v ?? '', array_intersect_key($p, $dados));
}

$erros = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $dados = array_merge($dados, array_intersect_key($_POST, $dados));
    $dados['depende_mare'] = isset($_POST['depende_mare']) ? 1 : 0;   // checkbox desmarcado não é enviado
    $erros = PontoTuristico::validar($dados);
    if (!$erros) {
        PontoTuristico::salvar($dados, $u['id'], $id);
        flash('sucesso', 'Ponto turístico salvo.');
        redirecionar('painel.php');
    }
}

$cidades = PontoTuristico::cidades();
$titulo = $id ? 'Editar ponto turístico' : 'Novo ponto turístico';
require __DIR__ . '/src/layout/topo.php';
?>
<p><a href="painel.php">← Meu painel</a></p>
<h1><?= e($titulo) ?></h1>
<?php if ($erros): ?>
    <div class="alerta alerta-erro"><ul><?php foreach ($erros as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="post" class="formulario cartao">
    <?= csrf_campo() ?>
    <label>Nome * <input type="text" name="nome" value="<?= e($dados['nome']) ?>" required></label>
    <div class="linha">
        <label>Cidade *
            <input type="text" name="cidade" value="<?= e($dados['cidade']) ?>" list="cidades" required>
            <datalist id="cidades"><?php foreach ($cidades as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?></datalist>
        </label>
        <label>Categoria *
            <select name="categoria" required>
                <option value="">Escolha…</option>
                <?php foreach (CATEGORIAS_PONTO as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= $dados['categoria'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>
    <label>Descrição * <textarea name="descricao" rows="4" required><?= e($dados['descricao']) ?></textarea></label>
    <div class="linha">
        <label>Bairro / localidade <input type="text" name="bairro" value="<?= e((string) $dados['bairro']) ?>"></label>
        <label>Endereço / referência <input type="text" name="endereco" value="<?= e((string) $dados['endereco']) ?>"></label>
    </div>
    <div class="linha">
        <label>Duração média da visita (min) * <input type="number" name="duracao_min" min="15" max="720" step="5" value="<?= e((string) $dados['duracao_min']) ?>" required></label>
        <label>Custo estimado por pessoa (R$) <input type="number" name="custo_estimado" min="0" step="0.01" value="<?= e((string) $dados['custo_estimado']) ?>"></label>
        <label>Faixa de preço
            <select name="faixa_preco">
                <option value="">Não sei</option>
                <?php foreach (FAIXAS_PRECO as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= $dados['faixa_preco'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>
    <div class="linha">
        <label>Melhor período
            <select name="melhor_periodo">
                <?php foreach (PERIODOS as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= $dados['melhor_periodo'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="checkbox"><input type="checkbox" name="depende_mare" value="1" <?= (int) $dados['depende_mare'] ? 'checked' : '' ?>> Depende da maré baixa</label>
    </div>
    <label>Tags de interesse (separadas por ;) <input type="text" name="tags" value="<?= e((string) $dados['tags']) ?>" placeholder="praia;natureza;familia;fotografia">
        <small class="muted">A IA usa as tags para combinar o lugar com o perfil do visitante.</small></label>
    <div class="linha">
        <label>Latitude <input type="text" name="latitude" inputmode="decimal" value="<?= e((string) $dados['latitude']) ?>" placeholder="-9.6658"></label>
        <label>Longitude <input type="text" name="longitude" inputmode="decimal" value="<?= e((string) $dados['longitude']) ?>" placeholder="-35.7350"></label>
    </div>
    <button class="botao botao-grande">Salvar</button>
</form>
<?php require __DIR__ . '/src/layout/rodape.php'; ?>
