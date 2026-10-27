<?php
require __DIR__ . '/src/bootstrap.php';

$destaques = array_slice(PontoTuristico::listar(), 0, 6);
$proximosEventos = array_slice(Evento::listarFuturos(), 0, 4);
$titulo = 'Início';
require __DIR__ . '/src/layout/topo.php';
?>
<section class="hero">
    <div>
        <p class="sobretitulo">Turismo e cultura de Alagoas</p>
        <h1>Seu roteiro por Alagoas, do seu jeito.</h1>
        <p class="lead">Diga quantos dias você tem e o que gosta. Nosso assistente de IA monta um roteiro dia a dia com praias, história, cultura e os eventos que acontecem durante a sua viagem.</p>
        <div class="acoes">
            <a href="roteiro_novo.php" class="botao">✦ Montar meu roteiro</a>
            <a href="pontos.php" class="botao botao-secundario">Explorar lugares</a>
        </div>
    </div>
</section>

<section>
    <div class="secao-titulo">
        <h2>Mais bem avaliados</h2>
        <a href="pontos.php">Ver todos →</a>
    </div>
    <div class="grade">
        <?php foreach ($destaques as $p): ?>
            <a class="cartao cartao-link" href="ponto.php?id=<?= (int) $p['id'] ?>">
                <span class="etiqueta etiqueta-<?= e($p['categoria']) ?>"><?= e(CATEGORIAS_PONTO[$p['categoria']]) ?></span>
                <h3><?= e($p['nome']) ?></h3>
                <p class="muted"><?= e($p['cidade']) ?> · <?= duracao((int) $p['duracao_min']) ?> · <?= dinheiro((float) $p['custo_estimado']) ?></p>
                <p><?= estrelas($p['nota_media'] !== null ? (float) $p['nota_media'] : null) ?></p>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section>
    <div class="secao-titulo">
        <h2>Próximos eventos</h2>
        <a href="eventos.php">Agenda completa →</a>
    </div>
    <div class="lista">
        <?php foreach ($proximosEventos as $ev): ?>
            <div class="item-lista">
                <div class="data-bloco">
                    <strong><?= date('d', strtotime($ev['inicio'])) ?></strong>
                    <span><?= date('m', strtotime($ev['inicio'])) ?>/<?= date('y', strtotime($ev['inicio'])) ?></span>
                </div>
                <div>
                    <h3><?= e($ev['titulo']) ?></h3>
                    <p class="muted"><?= e($ev['cidade']) ?> · <?= e($ev['local']) ?> · <?= date('H:i', strtotime($ev['inicio'])) ?> · <?= dinheiro((float) $ev['preco']) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (!$proximosEventos): ?><p class="muted">Nenhum evento agendado.</p><?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/src/layout/rodape.php'; ?>
