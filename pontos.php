<?php
require __DIR__ . '/src/bootstrap.php';

$filtros = [
    'cidade'    => $_GET['cidade'] ?? '',
    'categoria' => $_GET['categoria'] ?? '',
    'busca'     => trim($_GET['busca'] ?? ''),
];
$pontos = PontoTuristico::listar($filtros);
$cidades = PontoTuristico::cidades();
$titulo = 'Pontos turísticos';
require __DIR__ . '/src/layout/topo.php';
?>
<h1>Pontos turísticos</h1>

<form method="get" class="filtros">
    <input type="search" name="busca" placeholder="Buscar por nome, bairro ou interesse…" value="<?= e($filtros['busca']) ?>">
    <select name="cidade">
        <option value="">Todas as cidades</option>
        <?php foreach ($cidades as $c): ?>
            <option value="<?= e($c) ?>" <?= $filtros['cidade'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="categoria">
        <option value="">Todas as categorias</option>
        <?php foreach (CATEGORIAS_PONTO as $k => $v): ?>
            <option value="<?= e($k) ?>" <?= $filtros['categoria'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="botao">Filtrar</button>
    <?php if (array_filter($filtros)): ?><a href="pontos.php" class="link-limpar">Limpar</a><?php endif; ?>
</form>

<p class="muted"><?= count($pontos) ?> resultado(s)</p>

<div class="grade">
    <?php foreach ($pontos as $p): ?>
        <a class="cartao cartao-link" href="ponto.php?id=<?= (int) $p['id'] ?>">
            <span class="etiqueta etiqueta-<?= e($p['categoria']) ?>"><?= e(CATEGORIAS_PONTO[$p['categoria']]) ?></span>
            <h3><?= e($p['nome']) ?></h3>
            <p class="muted"><?= e($p['cidade']) ?><?= !empty($p['bairro']) ? ' (' . e($p['bairro']) . ')' : '' ?> · <?= duracao((int) $p['duracao_min']) ?> · <?= preco_ponto($p) ?><?= !empty($p['depende_mare']) ? ' · 🌊 maré baixa' : '' ?></p>
            <p class="resumo-texto"><?= e(mb_strimwidth($p['descricao'], 0, 110, '…')) ?></p>
            <p><?= estrelas($p['nota_media'] !== null ? (float) $p['nota_media'] : null) ?>
                <?php if ($p['total_avaliacoes']): ?><span class="muted">(<?= (int) $p['total_avaliacoes'] ?>)</span><?php endif; ?></p>
        </a>
    <?php endforeach; ?>
</div>
<?php if (!$pontos): ?>
    <div class="vazio">Nenhum ponto turístico encontrado com esses filtros.</div>
<?php endif; ?>
<?php require __DIR__ . '/src/layout/rodape.php'; ?>
