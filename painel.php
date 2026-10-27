<?php
require __DIR__ . '/src/bootstrap.php';
$u = exigir_login();
if ($u['perfil'] === 'turista') {
    redirecionar('meus_roteiros.php');
}
$ehGuia = $u['perfil'] === 'guia';

// Exclusão (só do que é do próprio usuário — RN01)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir'])) {
    csrf_validar();
    $ok = $ehGuia ? PontoTuristico::excluir((int) $_POST['excluir'], $u['id']) : Evento::excluir((int) $_POST['excluir'], $u['id']);
    $ok ? flash('sucesso', 'Registro excluído.') : flash('erro', 'Não foi possível excluir.');
    redirecionar('painel.php');
}

$itens = $ehGuia ? PontoTuristico::listar(['criado_por' => $u['id']]) : Evento::listarDoOrganizador($u['id']);
$titulo = 'Meu painel';
require __DIR__ . '/src/layout/topo.php';
?>
<div class="secao-titulo">
    <div>
        <p class="sobretitulo"><?= e(PERFIS[$u['perfil']]) ?></p>
        <h1><?= $ehGuia ? 'Meus pontos turísticos' : 'Meus eventos' ?></h1>
    </div>
    <a href="<?= $ehGuia ? 'ponto_form.php' : 'evento_form.php' ?>" class="botao">+ <?= $ehGuia ? 'Novo ponto turístico' : 'Novo evento' ?></a>
</div>

<div class="tabela-wrap">
<table class="tabela">
    <thead>
        <?php if ($ehGuia): ?>
            <tr><th>Nome</th><th>Cidade</th><th>Categoria</th><th>Nota</th><th></th></tr>
        <?php else: ?>
            <tr><th>Título</th><th>Cidade</th><th>Início</th><th>Preço</th><th></th></tr>
        <?php endif; ?>
    </thead>
    <tbody>
    <?php foreach ($itens as $i): ?>
        <tr>
            <?php if ($ehGuia): ?>
                <td><a href="ponto.php?id=<?= (int) $i['id'] ?>"><?= e($i['nome']) ?></a></td>
                <td><?= e($i['cidade']) ?></td>
                <td><?= e(CATEGORIAS_PONTO[$i['categoria']]) ?></td>
                <td><?= estrelas($i['nota_media'] !== null ? (float) $i['nota_media'] : null) ?></td>
            <?php else: ?>
                <td><?= e($i['titulo']) ?><?= strtotime($i['fim']) < time() ? ' <span class="muted">(encerrado)</span>' : '' ?></td>
                <td><?= e($i['cidade']) ?></td>
                <td><?= data_br($i['inicio'], true) ?></td>
                <td><?= dinheiro((float) $i['preco']) ?></td>
            <?php endif; ?>
            <td class="acoes-tabela">
                <a href="<?= $ehGuia ? 'ponto_form.php' : 'evento_form.php' ?>?id=<?= (int) $i['id'] ?>" class="botao botao-pequeno botao-secundario">Editar</a>
                <form method="post" onsubmit="return confirm('Excluir este registro?');">
                    <?= csrf_campo() ?>
                    <button name="excluir" value="<?= (int) $i['id'] ?>" class="botao botao-pequeno botao-perigo">Excluir</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php if (!$itens): ?><div class="vazio">Nada cadastrado ainda.</div><?php endif; ?>
<?php require __DIR__ . '/src/layout/rodape.php'; ?>
