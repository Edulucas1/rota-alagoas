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
[$marcadores, $semLocal] = Roteiro::marcadoresDoMapa($r);
// Cor de cada dia: a mesma no mapa e na linha do tempo
const CORES_DIA = ['#0f766e', '#ea6a2a', '#7c3aed', '#0369a1', '#be123c', '#4d7c0f', '#a16207'];
$corDia = fn(int $d) => CORES_DIA[($d - 1) % count(CORES_DIA)];
$numeroNoMapa = [];
foreach ($marcadores as $m) $numeroNoMapa[$m['dia'] . '-' . $m['id']] = $m['n'];
if ($marcadores) {
    $cabecalhoExtra = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" crossorigin="anonymous">';
}
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

<section class="mapa-roteiro nao-imprimir">
    <h2>Mapa do roteiro</h2>
    <?php if ($marcadores): ?>
        <div class="legenda-dias">
            <button type="button" class="chip-dia" data-dia="0">Todos os dias</button>
            <?php foreach (array_keys($r['dias_itens']) as $d): ?>
                <?php if (array_filter($marcadores, fn($m) => $m['dia'] === $d)): ?>
                    <button type="button" class="chip-dia" data-dia="<?= $d ?>" style="--cor: <?= $corDia($d) ?>">Dia <?= $d ?></button>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <div id="mapa" class="mapa" role="region" aria-label="Mapa com os pontos do roteiro"></div>
        <?php if ($semLocal): ?>
            <p class="muted pequeno">Sem localização no mapa: <?= e(implode(', ', $semLocal)) ?>. Os eventos também não aparecem no mapa.</p>
        <?php else: ?>
            <p class="muted pequeno">Os números seguem a ordem das atividades de cada dia. Eventos não aparecem no mapa.</p>
        <?php endif; ?>
    <?php else: ?>
        <p class="muted">Os pontos deste roteiro ainda não têm localização cadastrada.</p>
    <?php endif; ?>
</section>

<div class="dias">
    <?php foreach ($r['dias_itens'] as $dia => $itens): ?>
        <?php $dataDia = date('Y-m-d', strtotime($r['data_inicio'] . ' +' . ($dia - 1) . ' days')); ?>
        <section class="dia">
            <h2><span class="bolinha-dia" style="background: <?= $corDia($dia) ?>"></span>Dia <?= $dia ?> <span class="muted">· <?= data_br($dataDia) ?></span></h2>
            <?php if (!$itens): ?>
                <p class="muted">Dia livre para descansar ou explorar por conta própria.</p>
            <?php endif; ?>
            <ol class="linha-tempo">
                <?php foreach ($itens as $i): ?>
                    <li class="<?= $i['evento_id'] ? 'item-evento' : 'item-ponto' ?>">
                        <span class="hora"><?= $i['horario'] ? substr($i['horario'], 0, 5) : '—' ?></span>
                        <div>
                            <?php if ($i['ponto_id']): ?>
                                <?php $num = $numeroNoMapa[$dia . '-' . (int) $i['ponto_id']] ?? null; ?>
                                <?php if ($num): ?><span class="num-mapa" style="background: <?= $corDia($dia) ?>" title="Número no mapa"><?= $num ?></span><?php endif; ?>
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
<?php if ($marcadores): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js" crossorigin="anonymous"></script>
<script>
(function () {
    const marcadores = <?= json_encode($marcadores, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const cores = <?= json_encode(CORES_DIA) ?>;
    const caixa = document.getElementById('mapa');
    if (typeof L === 'undefined') {           // sem internet: a biblioteca do mapa não carregou
        caixa.outerHTML = '<p class="muted">O mapa precisa de internet para carregar.</p>';
        return;
    }
    const cor = d => cores[(d - 1) % cores.length];
    const esc = t => String(t).replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));
    const mapa = L.map(caixa, {scrollWheelZoom: false});
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(mapa);

    const porDia = {};
    marcadores.forEach(m => {
        const icone = L.divIcon({
            className: '', iconSize: [28, 28], iconAnchor: [14, 14], popupAnchor: [0, -14],
            html: '<span class="pino-mapa" style="background:' + cor(m.dia) + '">' + m.n + '</span>'
        });
        const pino = L.marker([m.lat, m.lng], {icon: icone, title: m.titulo})
            .bindPopup('<strong>Dia ' + m.dia + (m.horario ? ' · ' + m.horario : '') + '</strong><br>'
                + '<a href="ponto.php?id=' + m.id + '">' + esc(m.titulo) + '</a>');
        (porDia[m.dia] = porDia[m.dia] || {pinos: [], pontos: []});
        porDia[m.dia].pinos.push(pino);
        porDia[m.dia].pontos.push([m.lat, m.lng]);
    });

    const camadas = {};
    Object.keys(porDia).forEach(d => {
        const g = porDia[d];
        const linha = L.polyline(g.pontos, {color: cor(+d), weight: 3, opacity: 0.7, dashArray: '6 6'});
        camadas[d] = L.featureGroup([linha, ...g.pinos]).addTo(mapa);
    });

    const todos = L.featureGroup(Object.values(camadas));
    const enquadrar = grupo => mapa.fitBounds(grupo.getBounds(), {padding: [32, 32], maxZoom: 15});
    enquadrar(todos);

    // Botões da legenda: mostra só um dia (ou todos) e enquadra o mapa
    document.querySelectorAll('.chip-dia').forEach(botao => {
        botao.addEventListener('click', () => {
            const d = botao.dataset.dia;
            document.querySelectorAll('.chip-dia').forEach(b => b.classList.toggle('ativo', b === botao));
            Object.keys(camadas).forEach(k => {
                if (d === '0' || k === d) camadas[k].addTo(mapa); else mapa.removeLayer(camadas[k]);
            });
            enquadrar(d === '0' ? todos : camadas[d]);
        });
    });
    document.querySelector('.chip-dia[data-dia="0"]').classList.add('ativo');
})();
</script>
<?php endif; ?>
<?php require __DIR__ . '/src/layout/rodape.php'; ?>
