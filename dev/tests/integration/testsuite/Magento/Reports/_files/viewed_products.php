<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\App\AreaList::class)
    ->getArea('adminhtml')
    ->load(\Magento\Framework\App\Area::PART_CONFIG);

require __DIR__ . '/../../../Magento/Catalog/_files/product_simple.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_simple_duplicated.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_virtual.php';

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);

$simpleId = $productRepository->get('simple')->getId();
$simpleDuplicatedId = $productRepository->get('simple-1')->getId();
$virtualId = $productRepository->get('virtual-product')->getId();

$config = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\Framework\App\Config\MutableScopeConfigInterface::class
);
$config->setValue(
    'reports/options/enabled',
    1,
    \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT
);

// imitate product views
/** @var \Magento\Reports\Observer\CatalogProductViewObserver $reportObserver */
$reportObserver = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Reports\Observer\CatalogProductViewObserver::class
);

$productIds = [$simpleId, $simpleDuplicatedId, $simpleId, $virtualId, $simpleId, $virtualId];

foreach ($productIds as $productId) {
    $reportObserver->execute(
        new \Magento\Framework\Event\Observer(
            [
                'event' => new \Magento\Framework\DataObject(
                    [
                        'product' => new \Magento\Framework\DataObject(['id' => $productId]),
                        ]
                ),
            ]
        )
    );
}

// refresh report statistics
/** @var \Magento\Reports\Model\ResourceModel\Report\Product\Viewed $reportResource */
$reportResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Reports\Model\ResourceModel\Report\Product\Viewed::class
);
$reportResource->beginTransaction();
// prevent table truncation by incrementing the transaction nesting level counter
try {
    $reportResource->aggregate();
    $reportResource->commit();
} catch (\Exception $e) {
    $reportResource->rollBack();
    throw $e;
}
