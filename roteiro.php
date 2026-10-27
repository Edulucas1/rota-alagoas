<?php
require __DIR__ . '/src/bootstrap.php';
$u = exigir_login('turista');

$r = Roteiro::buscarComItens((int) ($_GET['id'] ?? 0), $u['id']);
if (!$r) {
    http_response_code(404);
    flash('erro', 'Roteiro não encontrado.');
    redirecionar('meus_roteiros.php');
}
$nomesInteresses = array_map(fn($i) => CATEGORIAS_PONTO[$i] ?? $i, explode(',', $r['interesses']));
$titulo = $r['titulo'];
require __DIR__ . '/src/layout/topo.php';
?>
<p class="nao-imprimir"><a href="meus_roteiros.php">← Meus roteiros</a></p>

<div class="cabecalho-roteiro">
    <div>
        <span class="selo <?= $r['origem'] === 'ia' ? 'selo-ia' : 'selo-padrao' ?>">
            <?= $r['origem'] === 'ia' ? '✦ Gerado por IA' : 'Gerado sem IA (plano B)' ?>
        </span>
        <h1><?= e($r['titulo']) ?></h1>
        <?php if ($r['resumo']): ?><p class="lead"><?= e($r['resumo']) ?></p><?php endif; ?>
        <p class="muted">
            <?= data_br($r['data_inicio']) ?> · <?= (int) $r['dias'] ?> dia(s) · Base: <?= e($r['cidade_base']) ?> ·
            <?= e(COMPANHIAS[$r['companhia']]) ?> · Ritmo <?= e($r['ritmo']) ?> · Orçamento <?= e(ORCAMENTOS[$r['orcamento']]) ?><br>
            Interesses: <?= e(implode(', ', $nomesInteresses)) ?>
        </p>
    </div>
    <div class="acoes nao-imprimir">
        <button class="botao botao-secundario" onclick="window.print()">Imprimir</button>
        <a href="roteiro_novo.php" class="botao">Novo roteiro</a>
    </div>
</div>

<div class="dias">
    <?php foreach ($r['dias_itens'] as $dia => $itens): ?>
        <?php $dataDia = date('Y-m-d', strtotime($r['data_inicio'] . ' +' . ($dia - 1) . ' days')); ?>
        <section class="dia">
            <h2>Dia <?= $dia ?> <span class="muted">· <?= data_br($dataDia) ?></span></h2>
            <?php if (!$itens): ?>
                <p class="muted">Dia livre para descansar ou explorar por conta própria.</p>
            <?php endif; ?>
            <ol class="linha-tempo">
                <?php foreach ($itens as $i): ?>
                    <li class="<?= $i['evento_id'] ? 'item-evento' : 'item-ponto' ?>">
                        <span class="hora"><?= $i['horario'] ? substr($i['horario'], 0, 5) : '—' ?></span>
                        <div>
                            <?php if ($i['ponto_id']): ?>
                                <a href="ponto.php?id=<?= (int) $i['ponto_id'] ?>"><strong><?= e($i['titulo']) ?></strong></a>
                                <span class="muted"> · <?= e($i['ponto_cidade']) ?> · <?= duracao((int) $i['duracao_min']) ?> · <?= dinheiro((float) $i['custo_estimado']) ?></span>
                            <?php elseif ($i['evento_id']): ?>
                                <span class="etiqueta">Evento</span> <strong><?= e($i['titulo']) ?></strong>
                                <span class="muted"> · <?= e($i['evento_cidade']) ?> · <?= e($i['evento_local']) ?> · <?= dinheiro((float) $i['evento_preco']) ?></span>
                            <?php else: ?>
                                <strong><?= e($i['titulo']) ?></strong> <span class="muted">(não está mais disponível)</span>
                            <?php endif; ?>
                            <?php if ($i['dica']): ?><p class="dica">💡 <?= e($i['dica']) ?></p><?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
        </section>
    <?php endforeach; ?>
</div>
<?php require __DIR__ . '/src/layout/rodape.php'; ?>
