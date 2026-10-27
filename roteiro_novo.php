<?php
require __DIR__ . '/src/bootstrap.php';
$u = exigir_login('turista');

$erros = [];
$dados = [
    'data_inicio' => date('Y-m-d', strtotime('+1 day')), 'dias' => 3, 'cidade_base' => 'Maceió',
    'interesses' => ['praia', 'cultura'], 'companhia' => 'casal', 'ritmo' => 'moderado', 'orcamento' => 'medio',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $dados = array_merge($dados, $_POST);
    $dados['interesses'] = (array) ($_POST['interesses'] ?? []);
    [$perfil, $erros] = Roteiro::validarPerfil($dados);

    if (!$erros) {
        $roteiro = Roteiro::gerar($perfil);
        $id = Roteiro::salvar($u['id'], $perfil, $roteiro);
        if (isset($roteiro['aviso'])) {
            flash('aviso', $roteiro['aviso']);
            if (config('debug')) flash('debug', 'Detalhe técnico: ' . $roteiro['erro_ia']);
        } else {
            flash('sucesso', 'Roteiro criado pelo assistente de IA e salvo em "Meus roteiros".');
        }
        redirecionar('roteiro.php?id=' . $id);
    }
}

$cidades = PontoTuristico::cidades();
$titulo = 'Montar roteiro';
require __DIR__ . '/src/layout/topo.php';
?>
<div class="cabecalho-ia">
    <p class="sobretitulo">✦ Assistente de roteiros</p>
    <h1>Monte seu roteiro por Alagoas</h1>
    <p class="lead">Conte um pouco sobre a sua viagem. A IA escolhe, entre os lugares e eventos cadastrados, o que combina com você e cabe no seu tempo.</p>
</div>

<?php if ($erros): ?>
    <div class="alerta alerta-erro"><ul><?php foreach ($erros as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="post" class="formulario cartao" id="form-roteiro">
    <?= csrf_campo() ?>
    <div class="linha">
        <label>Data de chegada
            <input type="date" name="data_inicio" value="<?= e($dados['data_inicio']) ?>" min="<?= date('Y-m-d') ?>" required>
        </label>
        <label>Quantos dias?
            <select name="dias">
                <?php for ($i = 1; $i <= 7; $i++): ?>
                    <option value="<?= $i ?>" <?= (int) $dados['dias'] === $i ? 'selected' : '' ?>><?= $i ?> dia<?= $i > 1 ? 's' : '' ?></option>
                <?php endfor; ?>
            </select>
        </label>
        <label>Onde vai se hospedar?
            <select name="cidade_base">
                <?php foreach ($cidades as $c): ?>
                    <option value="<?= e($c) ?>" <?= $dados['cidade_base'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>

    <fieldset>
        <legend>O que você gosta?</legend>
        <div class="chips">
            <?php foreach (CATEGORIAS_PONTO as $k => $v): ?>
                <label class="chip">
                    <input type="checkbox" name="interesses[]" value="<?= e($k) ?>" <?= in_array($k, $dados['interesses'], true) ? 'checked' : '' ?>>
                    <span><?= e($v) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </fieldset>

    <div class="linha">
        <label>Com quem?
            <select name="companhia">
                <?php foreach (COMPANHIAS as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= $dados['companhia'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Ritmo
            <select name="ritmo">
                <?php foreach (RITMOS as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= $dados['ritmo'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Orçamento
            <select name="orcamento">
                <?php foreach (ORCAMENTOS as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= $dados['orcamento'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>

    <button class="botao botao-grande" id="botao-gerar">✦ Gerar roteiro</button>
    <p class="carregando" id="carregando" hidden>⏳ O assistente está montando seu roteiro… isso leva alguns segundos.</p>
</form>

<script>
document.getElementById('form-roteiro').addEventListener('submit', function () {
    const b = document.getElementById('botao-gerar');
    b.disabled = true;
    b.textContent = 'Gerando…';
    document.getElementById('carregando').hidden = false;
});
</script>
<?php require __DIR__ . '/src/layout/rodape.php'; ?>
