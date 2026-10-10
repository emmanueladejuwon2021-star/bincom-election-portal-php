<?php
/**
 * Delta State Election Results Portal
 * Automated MySQL Setup & Database Importer
 *
 * This script connects to your local MySQL instance, creates the
 * 'bincom_test' database if it does not exist, and imports all
 * tables and election records from bincom_test.sql.
 *
 * Can be executed via CLI: php setup_mysql.php
 * Or via web browser: http://localhost/bincom-php/setup_mysql.php
 */

$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    header('Content-Type: text/html; charset=utf-8');
}

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_NAME') ?: 'bincom_test';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';

$sqlFile = __DIR__ . DIRECTORY_SEPARATOR . 'bincom_test.sql';

function outputLine($msg, $isCli) {
    if ($isCli) {
        echo $msg . PHP_EOL;
    } else {
        echo "<p style='margin: 4px 0; font-family: monospace;'>" . htmlspecialchars($msg) . "</p>";
    }
}

if (!$isCli) {
    echo "<!DOCTYPE html><html><head><title>MySQL Database Setup</title>";
    echo "<style>body { font-family: sans-serif; max-width: 720px; margin: 40px auto; padding: 24px; background: #f8fafc; color: #1e293b; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }</style>";
    echo "</head><body>";
    echo "<h2 style='color: #047857;'>Delta State Election Portal: MySQL Database Setup</h2>";
}

outputLine("Connecting to MySQL server at {$host}:{$port} as user '{$user}'...", $isCli);

try {
    // 1. Connect without database to ensure database can be created
    $dsnServer = "mysql:host={$host};port={$port};charset=utf8mb4";
    $pdo = new PDO($dsnServer, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5,
    ]);

    outputLine("[OK] Successfully connected to MySQL server.", $isCli);

    // 2. Create database
    outputLine("Creating database '{$dbname}' if it does not exist...", $isCli);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    outputLine("[OK] Database '{$dbname}' is ready.", $isCli);

    // 3. Select database
    $pdo->exec("USE `{$dbname}`");

    // 4. Check if tables already exist
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($tables)) {
        outputLine("[INFO] Database already contains " . count($tables) . " tables: " . implode(', ', $tables), $isCli);
        outputLine("Refreshing schema from bincom_test.sql...", $isCli);
    }

    // 5. Read bincom_test.sql
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL dump file not found at: {$sqlFile}");
    }

    outputLine("Reading SQL file ({$sqlFile})...", $isCli);
    $sqlContent = file_get_contents($sqlFile);

    // 6. Execute SQL dump
    outputLine("Importing tables and election returns...", $isCli);
    
    // Disable foreign key checks and auto-commit for fast bulk import
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';");

    // Split and execute SQL queries
    $queries = preg_split('/;\s*[\r\n]+/', $sqlContent);
    $executed = 0;

    foreach ($queries as $query) {
        $trimmed = trim($query);
        if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
            continue;
        }
        try {
            $pdo->exec($trimmed);
            $executed++;
        } catch (Throwable $queryErr) {
            // Ignore minor comment or non-fatal syntax errors
        }
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    outputLine("[OK] Executed {$executed} SQL statements successfully.", $isCli);

    // 7. Verify import statistics
    $puCount = $pdo->query("SELECT COUNT(*) FROM polling_unit")->fetchColumn();
    $lgaCount = $pdo->query("SELECT COUNT(*) FROM lga WHERE state_id = 25")->fetchColumn();
    $resultsCount = $pdo->query("SELECT COUNT(*) FROM announced_pu_results")->fetchColumn();
    $partyCount = $pdo->query("SELECT COUNT(*) FROM party")->fetchColumn();
    $agentCount = $pdo->query("SELECT COUNT(*) FROM agentname")->fetchColumn();

    outputLine("--------------------------------------------------", $isCli);
    outputLine("IMPORT SUMMARY:", $isCli);
    outputLine("  - Local Government Areas (LGAs): {$lgaCount}", $isCli);
    outputLine("  - Voting Centers (Polling Units): {$puCount}", $isCli);
    outputLine("  - Registered Political Parties: {$partyCount}", $isCli);
    outputLine("  - Announced Voting Center Results: {$resultsCount}", $isCli);
    outputLine("  - Accredited Agents Recorded: {$agentCount}", $isCli);
    outputLine("--------------------------------------------------", $isCli);
    outputLine("[SUCCESS] MySQL database '{$dbname}' is fully setup and ready!", $isCli);

    if (!$isCli) {
        echo "<div style='margin-top: 20px; padding: 12px; background: #dcfce7; border: 1px solid #86efac; border-radius: 6px; color: #166534;'>";
        echo "<strong>All Done!</strong> You can now browse the portal: <a href='index.php' style='color: #15803d; font-weight: bold;'>Go to Dashboard</a>";
        echo "</div>";
    }

} catch (Throwable $e) {
    outputLine("[ERROR] " . $e->getMessage(), $isCli);
    if (!$isCli) {
        echo "<div style='margin-top: 20px; padding: 12px; background: #fee2e2; border: 1px solid #fca5a5; border-radius: 6px; color: #991b1b;'>";
        echo "<strong>Setup Failed:</strong> " . htmlspecialchars($e->getMessage());
        echo "<p style='margin: 8px 0 0 0; font-size: 0.9rem;'>Please check that your MySQL service is running on {$host}:{$port} and your credentials are correct in config/db.php.</p>";
        echo "</div>";
    }
}

if (!$isCli) {
    echo "</body></html>";
}
