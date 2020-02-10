<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$checker = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\TestModuleCatalogSearch\Model\ElasticsearchVersionChecker::class
);
if ($checker->execute() === 7) {
    include __DIR__ . '/../../../Magento/CatalogSearch/_files/full_reindex.php';
}
