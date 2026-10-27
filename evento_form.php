<?php
require __DIR__ . '/src/bootstrap.php';
$u = exigir_login('organizador');

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$dados = ['titulo' => '', 'descricao' => '', 'cidade' => '', 'local' => '', 'inicio' => '', 'fim' => '', 'categoria' => '', 'preco' => 0];

if ($id !== null) {
    $ev = Evento::buscar($id);
    if (!$ev || (int) $ev['organizador_id'] !== $u['id']) {
        flash('erro', 'Você só pode editar eventos que cadastrou.');
        redirecionar('painel.php');
    }
    $dados = array_intersect_key($ev, $dados);
    // formato aceito pelo <input type="datetime-local">
    $dados['inicio'] = date('Y-m-d\TH:i', strtotime($ev['inicio']));
    $dados['fim'] = date('Y-m-d\TH:i', strtotime($ev['fim']));
}

$erros = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $dados = array_merge($dados, array_intersect_key($_POST, $dados));
    $erros = Evento::validar($dados);
    if (!$erros) {
        Evento::salvar($dados, $u['id'], $id);
        flash('sucesso', 'Evento salvo.');
        redirecionar('painel.php');
    }
}

$cidades = PontoTuristico::cidades();
$titulo = $id ? 'Editar evento' : 'Novo evento';
require __DIR__ . '/src/layout/topo.php';
?>
<p><a href="painel.php">← Meu painel</a></p>
<h1><?= e($titulo) ?></h1>
<?php if ($erros): ?>
    <div class="alerta alerta-erro"><ul><?php foreach ($erros as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="post" class="formulario cartao">
    <?= csrf_campo() ?>
    <label>Título * <input type="text" name="titulo" value="<?= e($dados['titulo']) ?>" required></label>
    <label>Descrição * <textarea name="descricao" rows="4" required><?= e($dados['descricao']) ?></textarea></label>
    <div class="linha">
        <label>Cidade *
            <input type="text" name="cidade" value="<?= e($dados['cidade']) ?>" list="cidades" required>
            <datalist id="cidades"><?php foreach ($cidades as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?></datalist>
        </label>
        <label>Local * <input type="text" name="local" value="<?= e($dados['local']) ?>" required></label>
    </div>
    <div class="linha">
        <label>Início * <input type="datetime-local" name="inicio" value="<?= e($dados['inicio']) ?>" required></label>
        <label>Fim * <input type="datetime-local" name="fim" value="<?= e($dados['fim']) ?>" required></label>
    </div>
    <div class="linha">
        <label>Categoria *
            <select name="categoria" required>
                <option value="">Escolha…</option>
                <?php foreach (CATEGORIAS_EVENTO as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= $dados['categoria'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Preço (R$, 0 = grátis) <input type="number" name="preco" min="0" step="0.01" value="<?= e((string) $dados['preco']) ?>"></label>
    </div>
    <button class="botao botao-grande">Salvar</button>
</form>
<?php require __DIR__ . '/src/layout/rodape.php'; ?>
