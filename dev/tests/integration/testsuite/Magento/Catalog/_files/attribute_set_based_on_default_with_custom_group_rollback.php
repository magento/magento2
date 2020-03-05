<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\TestFramework\Eav\Model\GetAttributeSetByName;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var GetAttributeSetByName $getAttributeSetByName */
$getAttributeSetByName = $objectManager->get(GetAttributeSetByName::class);
/** @var AttributeSetRepositoryInterface $attributeSetRepository */
$attributeSetRepository = $objectManager->get(AttributeSetRepositoryInterface::class);
$attributeSet = $getAttributeSetByName->execute('new_attribute_set');

if ($attributeSet) {
    $attributeSetRepository->delete($attributeSet);
}
