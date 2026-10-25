<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 *
 * PHP is single-threaded, so FileConcurrencyTest cannot call File::save() multiple
 * times simultaneously from within the test process. Instead, it launches this script
 * as multiple independent OS processes using popen(). Each process calls save() with
 * a unique hash entry. If the file locking in saveHashesToFile() is broken, concurrent
 * reads and writes will overwrite each other and the test will detect missing entries.
 *
 * phpcs:ignoreFile
 */

declare(strict_types=1);

use Magento\Csp\Model\SubresourceIntegrity\Storage\File;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\NullLogger;

$context = $argv[1] ?? '';
$staticDirPath = $argv[2] ?? '';
$workerId = (int)($argv[3] ?? 0);

if (empty($context) || empty($staticDirPath)) {
    fwrite(STDERR, "Missing arguments\n");
    exit(1);
}

require_once __DIR__ . '/../../../../../../../../../../vendor/autoload.php';

$bp = realpath(__DIR__ . '/../../../../../../../../../../');

$driverPool = new DriverPool();
$directoryList = new DirectoryList($bp, [
    DirectoryList::STATIC_VIEW => ['path' => $staticDirPath]
]);
$filesystem = new Filesystem(
    $directoryList,
    new ReadFactory($driverPool),
    new WriteFactory($driverPool)
);

$serializer = new Json();
$storage = new File($filesystem, new NullLogger(), $serializer);

// Random delay so all 5 processes reach the file open at roughly the same time.
usleep(random_int(1000, 10000));

$data = ["worker_{$workerId}.js" => "sha256-WORKER{$workerId}"];
$result = $storage->save($serializer->serialize($data), $context);

if (!$result) {
    fwrite(STDERR, "Worker {$workerId}: save() returned false\n");
    exit(1);
}

exit(0);
