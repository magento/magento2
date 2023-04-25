<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Customer\Model\Attribute;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Sales\Model\Order\Address;

$objectManager = Bootstrap::getObjectManager();
$addressData = [
    'region' => 'CA',
    'region_id' => '12',
    'postcode' => '11111',
    'lastname' => 'lastname',
    'firstname' => 'firstname',
    'street' => 'street',
    'city' => 'Los Angeles',
    'email' => 'multiattribute@example.com',
    'telephone' => '2222222',
    'country_id' => 'US'
];

/** @var $entityType Type */
$entityType = $objectManager->get(Config::class)
    ->getEntityType('customer_address');
/** @var $attributeSet Set */
$attributeSet = $objectManager->get(Set::class);

$attributeMultiselect = $objectManager->create(
    Attribute::class,
    [
        'data' => [
            'frontend_input' => 'multiselect',
            'frontend_label' => ['Multiselect Attribute'],
            'sort_order' => '0',
            'backend_type' => 'text',
            'is_user_defined' => 1,
            'is_system' => 0,
            'is_required' => '0',
            'is_visible' => '0',
            'attribute_set_id' => $entityType->getDefaultAttributeSetId(),
            'attribute_group_id' => $attributeSet->getDefaultGroupId($entityType->getDefaultAttributeSetId()),
            'entity_type_id' => $entityType->getId(),
            'backend_model' => ArrayBackend::class,
            'used_in_forms' => ['customer_register_address'],
            'option' => [
                'value' => [
                    'dog' => ['Dog'],
                    'cat' => ['Cat'],
                ],
                'order' => [
                    'dog' => 1,
                    'cat' => 2,
                ],
            ],
        ]
    ]
);

$attributeMultiselect->setAttributeCode('fixture_address_multiselect_attribute');
$attributeMultiselect->save();

$attributeMultiline = $objectManager->create(
    Attribute::class,
    [
        'data' => [
            'frontend_input' => 'multiline',
            'frontend_label' => ['Multiline Attribute'],
            'multiline_count' => 2,
            'sort_order' => '0',
            'backend_type' => 'varchar',
            'is_user_defined' => 1,
            'is_system' => 0,
            'is_required' => '0',
            'is_visible' => '0',
            'attribute_set_id' => $entityType->getDefaultAttributeSetId(),
            'attribute_group_id' => $attributeSet->getDefaultGroupId($entityType->getDefaultAttributeSetId()),
            'entity_type_id' => $entityType->getId(),
            'backend_model' => ArrayBackend::class,
            'used_in_forms' => ['customer_register_address'],
        ]
    ]
);

$attributeMultiline->setAttributeCode('fixture_address_multiline_attribute');
$attributeMultiline->save();

$billingAddress = $objectManager->create(
    Address::class,
    ['data' => $addressData]
);
$billingAddress->setAddressType('billing');
$billingAddress->save();
