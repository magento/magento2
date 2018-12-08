<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
$attributeSet = $objectManager->create(\Magento\Eav\Model\Entity\Attribute\Set::class)
    ->load('Super Powerful Muffins', 'attribute_set_name');
if ($attributeSet->getId()) {
    $attributeSet->delete();
}

$attributeSet = $objectManager->create(\Magento\Eav\Model\Entity\Attribute\Set::class)
    ->load('Banana Rangers', 'attribute_set_name');
if ($attributeSet->getId()) {
    $attributeSet->delete();
}

$attributeSet = $objectManager->create(\Magento\Eav\Model\Entity\Attribute\Set::class)
    ->load('Guardians of the Refrigerator', 'attribute_set_name');
if ($attributeSet->getId()) {
    $attributeSet->delete();
}
