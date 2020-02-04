<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

include __DIR__ . '/product_simple_rollback.php';


/** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $model */
$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
);
$model->load('attribute_code_custom', 'attribute_code')->delete();
