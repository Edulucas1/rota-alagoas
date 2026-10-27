<?php
require __DIR__ . '/src/bootstrap.php';
$u = exigir_login('turista');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir'])) {
    csrf_validar();
    Roteiro::excluir((int) $_POST['excluir'], $u['id'])
        ? flash('sucesso', 'Roteiro excluído.')
        : flash('erro', 'Roteiro não encontrado.');
    redirecionar('meus_roteiros.php');
}

$roteiros = Roteiro::listarDoUsuario($u['id']);
$titulo = 'Meus roteiros';
require __DIR__ . '/src/layout/topo.php';
?>
<div class="secao-titulo">
    <h1>Meus roteiros</h1>
    <a href="roteiro_novo.php" class="botao">✦ Novo roteiro</a>
</div>

<div class="lista">
    <?php foreach ($roteiros as $r): ?>
        <div class="item-lista">
            <div class="flex-1">
                <span class="selo <?= $r['origem'] === 'ia' ? 'selo-ia' : 'selo-padrao' ?>"><?= $r['origem'] === 'ia' ? '✦ IA' : 'Plano B' ?></span>
                <h3><a href="roteiro.php?id=<?= (int) $r['id'] ?>"><?= e($r['titulo']) ?></a></h3>
                <p class="muted">Chegada <?= data_br($r['data_inicio']) ?> · <?= (int) $r['dias'] ?> dia(s) · <?= (int) $r['total_itens'] ?> atividade(s) · criado em <?= data_br($r['criado_em'], true) ?></p>
            </div>
            <div class="acoes">
                <a href="roteiro.php?id=<?= (int) $r['id'] ?>" class="botao botao-pequeno">Abrir</a>
                <form method="post" onsubmit="return confirm('Excluir este roteiro?');">
                    <?= csrf_campo() ?>
                    <button name="excluir" value="<?= (int) $r['id'] ?>" class="botao botao-pequeno botao-perigo">Excluir</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php if (!$roteiros): ?>
    <div class="vazio">Você ainda não tem roteiros. <a href="roteiro_novo.php">Monte o primeiro!</a></div>
<?php endif; ?>
<?php require __DIR__ . '/src/layout/rodape.php'; ?>
