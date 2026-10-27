<?php
/** @var string $titulo */
$u = usuario_atual();
$pagina = basename($_SERVER['SCRIPT_NAME'] ?? '');
$ativo = fn(string ...$arqs) => in_array($pagina, $arqs, true) ? ' class="ativo"' : '';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo ?? 'Rota Alagoas') ?> · Rota Alagoas</title>
    <link rel="stylesheet" href="assets/style.css">
    <?= $cabecalhoExtra ?? '' ?>
</head>
<body>
<header class="topo">
    <div class="container topo-inner">
        <a href="index.php" class="marca"><span class="marca-icone">◆</span> Rota Alagoas</a>
        <input type="checkbox" id="menu-toggle" hidden>
        <label for="menu-toggle" class="menu-botao" aria-label="Abrir menu">☰</label>
        <nav class="menu">
            <a href="pontos.php"<?= $ativo('pontos.php', 'ponto.php') ?>>Pontos turísticos</a>
            <a href="eventos.php"<?= $ativo('eventos.php') ?>>Eventos</a>
            <a href="roteiro_novo.php" class="destaque">✦ Montar roteiro</a>
            <?php if ($u): ?>
                <?php if ($u['perfil'] === 'turista'): ?>
                    <a href="meus_roteiros.php"<?= $ativo('meus_roteiros.php', 'roteiro.php') ?>>Meus roteiros</a>
                <?php else: ?>
                    <a href="painel.php"<?= $ativo('painel.php', 'ponto_form.php', 'evento_form.php') ?>>Meu painel</a>
                <?php endif; ?>
                <span class="usuario-chip" title="<?= e(PERFIS[$u['perfil']]) ?>"><?= e(explode(' ', $u['nome'])[0]) ?> · <?= e(PERFIS[$u['perfil']]) ?></span>
                <a href="logout.php">Sair</a>
            <?php else: ?>
                <a href="login.php"<?= $ativo('login.php') ?>>Entrar</a>
                <a href="cadastro.php" class="botao botao-pequeno">Criar conta</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
<?php foreach (pegar_flashes() as $f): ?>
    <div class="alerta alerta-<?= e($f['tipo']) ?>"><?= e($f['msg']) ?></div>
<?php endforeach; ?>
