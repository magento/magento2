<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$checker = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\TestModuleCatalogSearch\Model\ElasticsearchVersionChecker::class
);
if ($checker->getVersion() === 6) {
    Resolver::getInstance()->requireDataFixture('Magento/CatalogSearch/_files/full_reindex.php');
}
