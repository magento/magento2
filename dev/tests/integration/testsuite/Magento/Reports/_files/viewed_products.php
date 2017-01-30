<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\AreaList')
    ->getArea('adminhtml')
    ->load(\Magento\Framework\App\Area::PART_CONFIG);

require __DIR__ . '/../../../Magento/Catalog/_files/product_simple.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_simple_duplicated.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_virtual.php';

// imitate product views
/** @var \Magento\Reports\Observer\CatalogProductViewObserver $reportObserver */
$reportObserver = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Reports\Observer\CatalogProductViewObserver'
);
foreach ([1, 2, 1, 21, 1, 21] as $productId) {
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
    'Magento\Reports\Model\ResourceModel\Report\Product\Viewed'
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
