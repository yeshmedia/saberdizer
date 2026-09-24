<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit(1);
}

require dirname(__DIR__) . '/app/database.php';

try {
    $sql = file_get_contents(dirname(__DIR__) . '/database/001_leads.sql');
    if ($sql === false) {
        throw new RuntimeException('Migration file is missing.');
    }
    database()->exec($sql);
    fwrite(STDOUT, "Migration complete.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, "Migration failed: " . $exception->getMessage() . "\n");
    exit(1);
}
