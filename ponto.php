<?php
require __DIR__ . '/src/bootstrap.php';

$id = (int) ($_GET['id'] ?? 0);
$u = usuario_atual();

// Envio de avaliação (UC02)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $u = exigir_login('turista');
    $erros = Avaliacao::avaliar($u['id'], $id, (int) ($_POST['nota'] ?? 0), (string) ($_POST['comentario'] ?? ''));
    $erros ? flash('erro', implode(' ', $erros)) : flash('sucesso', 'Obrigado! Sua avaliação foi registrada.');
    redirecionar('ponto.php?id=' . $id);
}

$p = PontoTuristico::buscar($id);
if (!$p) {
    http_response_code(404);
    flash('erro', 'Ponto turístico não encontrado.');
    redirecionar('pontos.php');
}
$avaliacoes = Avaliacao::doPonto($id);
$minha = ($u && $u['perfil'] === 'turista') ? Avaliacao::doUsuarioNoPonto($u['id'], $id) : null;
$titulo = $p['nome'];
require __DIR__ . '/src/layout/topo.php';
?>
<p><a href="pontos.php">← Pontos turísticos</a></p>

<article class="detalhe">
    <span class="etiqueta etiqueta-<?= e($p['categoria']) ?>"><?= e(CATEGORIAS_PONTO[$p['categoria']]) ?></span>
    <h1><?= e($p['nome']) ?></h1>
    <p class="lead"><?= e($p['cidade']) ?><?= !empty($p['bairro']) ? ' · ' . e($p['bairro']) : '' ?><?= $p['endereco'] ? ' · ' . e($p['endereco']) : '' ?></p>

    <div class="fatos">
        <div><span>Nota</span><strong><?= estrelas($p['nota_media'] !== null ? (float) $p['nota_media'] : null) ?></strong></div>
        <div><span>Tempo de visita</span><strong><?= duracao((int) $p['duracao_min']) ?></strong></div>
        <div><span>Custo estimado</span><strong><?= preco_ponto($p) ?></strong></div>
        <div><span>Melhor período</span><strong><?= e(PERIODOS[$p['melhor_periodo'] ?? 'qualquer'] ?? 'Qualquer horário') ?></strong></div>
        <div><span>Cadastrado por</span><strong><?= e($p['guia_nome']) ?></strong></div>
    </div>

    <?php if (!empty($p['depende_mare'])): ?>
        <div class="alerta alerta-aviso">🌊 Este passeio depende da <strong>maré baixa</strong>. Confira a tábua de marés antes de ir.</div>
    <?php endif; ?>

    <p><?= nl2br(e($p['descricao'])) ?></p>

    <?php if ($tags = tags_ponto($p)): ?>
        <p class="tags"><?php foreach ($tags as $t): ?><span class="tag"><?= e(str_replace('_', ' ', $t)) ?></span><?php endforeach; ?></p>
    <?php endif; ?>
    <?php
    $mapa = ($p['latitude'] ?? null) !== null && ($p['longitude'] ?? null) !== null
        ? $p['latitude'] . ',' . $p['longitude']
        : $p['nome'] . ', ' . $p['cidade'] . ', Alagoas';
    ?>
    <p><a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($mapa) ?>" target="_blank" rel="noopener">📍 Ver no mapa</a></p>
</article>

<section class="duas-colunas">
    <div>
        <h2>Avaliações (<?= count($avaliacoes) ?>)</h2>
        <?php foreach ($avaliacoes as $a): ?>
            <div class="avaliacao">
                <strong><?= e($a['usuario_nome']) ?></strong>
                <span class="estrelas"><?= str_repeat('★', (int) $a['nota']) . str_repeat('☆', 5 - (int) $a['nota']) ?></span>
                <span class="muted"><?= data_br($a['criado_em']) ?></span>
                <?php if ($a['comentario']): ?><p><?= e($a['comentario']) ?></p><?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php if (!$avaliacoes): ?><p class="muted">Ainda não há avaliações. Seja o primeiro!</p><?php endif; ?>
    </div>

    <div>
        <div class="cartao">
            <h2><?= $minha ? 'Atualizar sua avaliação' : 'Avaliar este lugar' ?></h2>
            <?php if ($u && $u['perfil'] === 'turista'): ?>
                <form method="post">
                    <?= csrf_campo() ?>
                    <fieldset class="nota-escolha">
                        <legend>Sua nota</legend>
                        <?php for ($n = 5; $n >= 1; $n--): ?>
                            <input type="radio" id="nota<?= $n ?>" name="nota" value="<?= $n ?>" <?= ($minha && (int) $minha['nota'] === $n) ? 'checked' : '' ?> required>
                            <label for="nota<?= $n ?>" title="<?= $n ?> estrela(s)">★</label>
                        <?php endfor; ?>
                    </fieldset>
                    <label>Comentário (opcional)
                        <textarea name="comentario" rows="3" maxlength="500"><?= e($minha['comentario'] ?? '') ?></textarea>
                    </label>
                    <button class="botao">Enviar avaliação</button>
                </form>
            <?php else: ?>
                <p class="muted">Entre como <strong>turista</strong> para avaliar.</p>
                <?php if (!$u): ?><a href="login.php?voltar=<?= urlencode('ponto.php?id=' . $id) ?>" class="botao botao-secundario">Entrar</a><?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require __DIR__ . '/src/layout/rodape.php'; ?>
