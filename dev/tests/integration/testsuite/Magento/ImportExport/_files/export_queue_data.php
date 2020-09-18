<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\ImportExport\Model\Export\Entity\ExportInfoFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/second_product_simple.php');

$objectManager = Bootstrap::getObjectManager();
/** @var ExportInfoFactory $exportInfoFactory */
$exportInfoFactory = $objectManager->get(ExportInfoFactory::class);
/** @var PublisherInterface $messagePublisher */
$messagePublisher = $objectManager->get(PublisherInterface::class);
$params = [
    'file_format' => 'csv',
    'entity' => ProductAttributeInterface::ENTITY_TYPE_CODE,
    'export_filter' => [ProductInterface::SKU => 'simple2'],
    'skip_attr' => [],
];
$dataObject = $exportInfoFactory->create(
    $params['file_format'],
    $params['entity'],
    $params['export_filter'],
    $params['skip_attr']
);
$messagePublisher->publish('import_export.export', $dataObject);
