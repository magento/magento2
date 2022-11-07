<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Widget\Model\ResourceModel\Widget\Instance;
use Magento\Widget\Model\ResourceModel\Widget\Instance\CollectionFactory;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();
/** @var CollectionFactory $collectionFactory */
$collectionFactory = $objectManager->get(CollectionFactory::class);
/** @var Instance $widgetResourceModel */
$widgetResourceModel = $objectManager->get(Instance::class);

$titles = ['cms page widget title', 'product link widget title', 'recently compared products'];
$widgets = $collectionFactory->create()->addFieldToFilter('title', $titles);
foreach ($widgets as $widget) {
    $widgetResourceModel->delete($widget);
}

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/second_product_simple_rollback.php');
