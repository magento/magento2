<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\ObjectManagerInterface $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Customer\Model\AttributeFactory $attributeFactory */
$attributeFactory = $objectManager->create(\Magento\Customer\Model\AttributeFactory::class);

/** @var \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository */
$attributeRepository =  $objectManager->create(\Magento\Eav\Api\AttributeRepositoryInterface::class);

/** @var \Magento\Customer\Setup\CustomerSetup $setupResource */
$setupResource = $objectManager->create(\Magento\Customer\Setup\CustomerSetup::class);

$attributeNames = ['custom_attribute1', 'custom_attribute2'];
foreach ($attributeNames as $attributeName) {
    /** @var \Magento\Customer\Model\Attribute $attribute */
    $attribute = $attributeFactory->create();

    $attribute->setName($attributeName)
        ->setEntityTypeId(2)
        ->setAttributeSetId(2)
        ->setAttributeGroupId(1)
        ->setFrontendInput('text')
        ->setFrontendLabel('custom_attribute_frontend_label')
        ->setIsUserDefined(true);

    $attributeRepository->save($attribute);

    $setupResource->getSetup()
        ->getConnection()
        ->insertMultiple(
            $setupResource->getSetup()->getTable('customer_form_attribute'),
            [['form_code' => 'customer_address_edit', 'attribute_id' => $attribute->getAttributeId()]]
        );
}
