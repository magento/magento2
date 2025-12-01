<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple_rollback.php');

/** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $model */
$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
);
$model->load('attribute_code_custom', 'attribute_code')->delete();
