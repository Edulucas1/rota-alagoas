<?php
require __DIR__ . '/src/bootstrap.php';

$filtros = [
    'cidade' => $_GET['cidade'] ?? '',
    'de'     => $_GET['de'] ?? '',
    'ate'    => $_GET['ate'] ?? '',
];
$eventos = Evento::listarFuturos($filtros);
$cidades = Evento::cidades();
$titulo = 'Eventos';
require __DIR__ . '/src/layout/topo.php';
?>
<h1>Agenda de eventos</h1>

<form method="get" class="filtros">
    <select name="cidade">
        <option value="">Todas as cidades</option>
        <?php foreach ($cidades as $c): ?>
            <option value="<?= e($c) ?>" <?= $filtros['cidade'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
        <?php endforeach; ?>
    </select>
    <label class="inline">De <input type="date" name="de" value="<?= e($filtros['de']) ?>"></label>
    <label class="inline">Até <input type="date" name="ate" value="<?= e($filtros['ate']) ?>"></label>
    <button class="botao">Filtrar</button>
    <?php if (array_filter($filtros)): ?><a href="eventos.php" class="link-limpar">Limpar</a><?php endif; ?>
</form>

<div class="lista">
    <?php foreach ($eventos as $ev): ?>
        <div class="item-lista">
            <div class="data-bloco">
                <strong><?= date('d', strtotime($ev['inicio'])) ?></strong>
                <span><?= date('m', strtotime($ev['inicio'])) ?>/<?= date('y', strtotime($ev['inicio'])) ?></span>
            </div>
            <div class="flex-1">
                <span class="etiqueta"><?= e(CATEGORIAS_EVENTO[$ev['categoria']]) ?></span>
                <h3><?= e($ev['titulo']) ?></h3>
                <p class="muted"><?= e($ev['cidade']) ?> · <?= e($ev['local']) ?> · <?= data_br($ev['inicio'], true) ?> às <?= date('H:i', strtotime($ev['fim'])) ?> · <?= dinheiro((float) $ev['preco']) ?></p>
                <p><?= e($ev['descricao']) ?></p>
                <p class="muted pequeno">Organização: <?= e($ev['organizador_nome']) ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php if (!$eventos): ?>
    <div class="vazio">Nenhum evento encontrado no período.</div>
<?php endif; ?>
<?php require __DIR__ . '/src/layout/rodape.php'; ?>
