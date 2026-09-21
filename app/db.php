<?php
/**
 * SQL Server connection helper. Reads standard DB_* environment variables
 * (injected from a Secret in Module 1.8). Returns [PDO|null, string status].
 */
function db_config(): ?array {
    $host = getenv('DB_HOST');
    if (!$host) { return null; }
    return [
        'host' => $host,
        'port' => getenv('DB_PORT') ?: '1433',
        'name' => getenv('DB_NAME') ?: 'phpdemo',
        'user' => getenv('DB_USER') ?: 'sa',
        'pass' => getenv('DB_PASSWORD') ?: '',
    ];
}

function db_connect(): array {
    $cfg = db_config();
    if ($cfg === null) {
        return [null, 'not configured — complete Module 1.8'];
    }
    if (!extension_loaded('pdo_sqlsrv')) {
        return [null, 'pdo_sqlsrv extension missing — build with the provided Dockerfile'];
    }
    $dsn = sprintf(
        'sqlsrv:Server=%s,%s;Database=%s;TrustServerCertificate=1;LoginTimeout=3',
        $cfg['host'], $cfg['port'], $cfg['name']
    );
    try {
        $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        return [$pdo, 'connected'];
    } catch (PDOException $e) {
        return [null, 'connection failed: ' . $e->getMessage()];
    }
}

/** Create the visits table if needed, record a visit, return total count. */
function db_record_visit(PDO $pdo): int {
    $pdo->exec("IF OBJECT_ID('dbo.visits','U') IS NULL
                CREATE TABLE dbo.visits (id INT IDENTITY PRIMARY KEY,
                                         pod NVARCHAR(128),
                                         ts DATETIME2 DEFAULT SYSUTCDATETIME())");
    $stmt = $pdo->prepare('INSERT INTO dbo.visits (pod) VALUES (?)');
    $stmt->execute([gethostname()]);
    return (int) $pdo->query('SELECT COUNT(*) FROM dbo.visits')->fetchColumn();
}
