<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var Magento\Customer\Model\Attribute $dateAttribute */
$dateAttribute = $objectManager->create(\Magento\Customer\Model\Attribute::class);
$dateAttribute->setName('date')
    ->setEntityTypeId(1)
    ->setIsUserDefined(1)
    ->setAttributeSetId(1)
    ->setAttributeGroupId(1)
    ->setAttributeCode('date')
    ->setFrontendInput('date')
    ->setFrontendLabel('date_attribute_frontend_label')
    ->setFrontendModel('Magento\Eav\Model\Entity\Attribute\Frontend\Datetime')
    ->setBackendType('datetime')
    ->setBackendModel('Magento\Eav\Model\Entity\Attribute\Backend\Datetime')
    ->setSortOrder(42)
    ->save();
