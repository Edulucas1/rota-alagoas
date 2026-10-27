<?php
declare(strict_types=1);

/** Conexão única (singleton) com o MySQL via PDO. */
class Database
{
    private static ?PDO $pdo = null;

    public static function conexao(): PDO
    {
        if (self::$pdo === null) {
            $c = config('db');
            $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $c['host'], $c['porta'] ?? 3306, $c['nome']);
            try {
                self::$pdo = new PDO($dsn, $c['usuario'], $c['senha'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $ex) {
                http_response_code(500);
                exit('<h2>Não foi possível conectar ao banco de dados.</h2><p>Confira se o MySQL está ligado no XAMPP e se o banco <code>'
                    . e($c['nome']) . '</code> foi importado (database/rota_alagoas.sql).</p>'
                    . (config('debug') ? '<pre>' . e($ex->getMessage()) . '</pre>' : ''));
            }
        }
        return self::$pdo;
    }

    /** Atalho: prepara, executa e devolve o statement. */
    public static function executar(string $sql, array $parametros = []): PDOStatement
    {
        $st = self::conexao()->prepare($sql);
        $st->execute($parametros);
        return $st;
    }
}
