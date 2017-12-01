<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../Eav/_files/empty_attribute_set.php';
require __DIR__ . '/../../Catalog/_files/product_simple.php';

$product->setAttributeSetId($attributeSet->getId());
$product->save();
