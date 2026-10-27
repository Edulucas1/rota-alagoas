<?php
require __DIR__ . '/src/bootstrap.php';
$_SESSION = [];
session_regenerate_id(true);
flash('sucesso', 'Você saiu da sua conta.');
redirecionar('index.php');
