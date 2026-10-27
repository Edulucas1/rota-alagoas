<?php
require __DIR__ . '/src/bootstrap.php';

$dados = ['nome' => '', 'email' => '', 'perfil' => 'turista'];
$erros = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $dados = array_merge($dados, array_intersect_key($_POST, $dados));
    [$id, $erros] = Usuario::cadastrar($dados['nome'], $dados['email'], (string) ($_POST['senha'] ?? ''), $dados['perfil']);
    if ($id) {
        session_regenerate_id(true);
        $_SESSION['usuario'] = Usuario::buscarPorId($id);
        flash('sucesso', 'Conta criada! Bem-vindo(a) ao Rota Alagoas.');
        redirecionar($dados['perfil'] === 'turista' ? 'roteiro_novo.php' : 'painel.php');
    }
}

$titulo = 'Criar conta';
require __DIR__ . '/src/layout/topo.php';
?>
<div class="caixa-estreita">
    <h1>Criar conta</h1>
    <?php if ($erros): ?>
        <div class="alerta alerta-erro"><ul><?php foreach ($erros as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="post" class="formulario cartao">
        <?= csrf_campo() ?>
        <label>Nome <input type="text" name="nome" value="<?= e($dados['nome']) ?>" required minlength="3"></label>
        <label>E-mail <input type="email" name="email" value="<?= e($dados['email']) ?>" required></label>
        <label>Senha <input type="password" name="senha" required minlength="6"></label>
        <fieldset>
            <legend>Eu sou…</legend>
            <?php foreach (PERFIS as $k => $v): ?>
                <label class="radio"><input type="radio" name="perfil" value="<?= e($k) ?>" <?= $dados['perfil'] === $k ? 'checked' : '' ?>> <?= e($v) ?></label>
            <?php endforeach; ?>
        </fieldset>
        <button class="botao botao-grande">Criar conta</button>
        <p class="muted">Já tem conta? <a href="login.php">Entrar</a></p>
    </form>
</div>
<?php require __DIR__ . '/src/layout/rodape.php'; ?>
