<?php
declare(strict_types=1);

class Usuario
{
    /** @return array{0: ?int, 1: array} [id criado, erros] */
    public static function cadastrar(string $nome, string $email, string $senha, string $perfil): array
    {
        $erros = [];
        $nome = trim($nome);
        $email = mb_strtolower(trim($email));

        if (mb_strlen($nome) < 3) $erros[] = 'Informe seu nome (mínimo 3 letras).';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
        if (strlen($senha) < 6) $erros[] = 'A senha precisa ter pelo menos 6 caracteres.';
        if (!array_key_exists($perfil, PERFIS)) $erros[] = 'Perfil inválido.';
        if (!$erros && self::buscarPorEmail($email)) $erros[] = 'Já existe uma conta com este e-mail.';
        if ($erros) return [null, $erros];

        Database::executar(
            'INSERT INTO usuarios (nome, email, senha_hash, perfil) VALUES (?, ?, ?, ?)',
            [$nome, $email, password_hash($senha, PASSWORD_DEFAULT), $perfil]
        );
        return [(int) Database::conexao()->lastInsertId(), []];
    }

    /** Devolve o usuário (sem o hash) se e-mail e senha conferem. */
    public static function autenticar(string $email, string $senha): ?array
    {
        $u = self::buscarPorEmail(mb_strtolower(trim($email)));
        if (!$u || !password_verify($senha, $u['senha_hash'])) {
            return null;
        }
        unset($u['senha_hash']);
        return $u;
    }

    public static function buscarPorEmail(string $email): ?array
    {
        $u = Database::executar('SELECT * FROM usuarios WHERE email = ?', [$email])->fetch();
        return $u ?: null;
    }

    public static function buscarPorId(int $id): ?array
    {
        $u = Database::executar('SELECT id, nome, email, perfil, criado_em FROM usuarios WHERE id = ?', [$id])->fetch();
        return $u ?: null;
    }
}
