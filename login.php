<?php
require __DIR__ . '/src/bootstrap.php';

// Só aceita voltar para páginas internas (evita redirecionamento aberto)
$bruto = (string) ($_GET['voltar'] ?? $_POST['voltar'] ?? '');
$arquivo = basename((string) parse_url($bruto, PHP_URL_PATH));
$query = (string) parse_url($bruto, PHP_URL_QUERY);
$voltar = preg_match('/^[a-z_]+\.php$/', $arquivo) && preg_match('/^[\w=&%.-]*$/', $query)
    ? $arquivo . ($query !== '' ? '?' . $query : '')
    : '';

$email = '';
$erro = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $email = (string) ($_POST['email'] ?? '');
    $usuario = Usuario::autenticar($email, (string) ($_POST['senha'] ?? ''));
    if ($usuario) {
        session_regenerate_id(true);
        $_SESSION['usuario'] = $usuario;
        flash('sucesso', 'Olá, ' . explode(' ', $usuario['nome'])[0] . '!');
        redirecionar($voltar ?: ($usuario['perfil'] === 'turista' ? 'index.php' : 'painel.php'));
    }
    $erro = 'E-mail ou senha incorretos.';
}

$titulo = 'Entrar';
require __DIR__ . '/src/layout/topo.php';
?>
<div class="caixa-estreita">
    <h1>Entrar</h1>
    <?php if ($erro): ?><div class="alerta alerta-erro"><?= e($erro) ?></div><?php endif; ?>
    <form method="post" class="formulario cartao">
        <?= csrf_campo() ?>
        <input type="hidden" name="voltar" value="<?= e($voltar) ?>">
        <label>E-mail <input type="email" name="email" value="<?= e($email) ?>" required autofocus></label>
        <label>Senha <input type="password" name="senha" required></label>
        <button class="botao botao-grande">Entrar</button>
        <p class="muted">Não tem conta? <a href="cadastro.php">Cadastre-se</a></p>
    </form>
    <div class="cartao dica-demo">
        <strong>Contas de demonstração</strong> (senha <code>123456</code>)
        <ul>
            <li>Turista: <code>ana@rota.al</code></li>
            <li>Guia local: <code>guia@rota.al</code></li>
            <li>Organizador: <code>org@rota.al</code></li>
        </ul>
    </div>
</div>
<?php require __DIR__ . '/src/layout/rodape.php'; ?>
