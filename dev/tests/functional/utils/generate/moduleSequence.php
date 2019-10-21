<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../../../app/bootstrap.php';

$magentoObjectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, $_SERVER);
$magentoObjectManager = $magentoObjectManagerFactory->create($_SERVER);
$magentoComponentSequence = $magentoObjectManager->create(\Magento\Framework\Module\ModuleList\Loader::class)->load();
if (!file_exists(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'generated')) {
    mkdir(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'generated');
}
file_put_contents(
    dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'generated' . DIRECTORY_SEPARATOR . 'moduleSequence.json',
    json_encode($magentoComponentSequence, JSON_PRETTY_PRINT)
);
