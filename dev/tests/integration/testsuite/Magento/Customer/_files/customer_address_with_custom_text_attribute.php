<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Model\Attribute;
use Magento\Eav\Model\Entity\Attribute\Set;

$objectManager = Bootstrap::getObjectManager();
/** @var $entityType Type */
$entityType = $objectManager
    ->create(Config::class)
    ->getEntityType('customer');

/** @var $attributeSet Set */
$attributeSet = Bootstrap::getObjectManager()
    ->create(Set::class);

$select = Bootstrap::getObjectManager()->create(
    Attribute::class,
    [
        'data' => [
            'frontend_input' => 'text',
            'frontend_label' => ['test_text_attribute'],
            'sort_order' => 1,
            'backend_type' => 'varchar',
            'is_user_defined' => 1,
            'is_system' => 0,
            'is_used_in_grid' => 1,
            'is_required' => '0',
            'is_visible' => 1,
            'used_in_forms' => [
                'customer_address_edit',
                'adminhtml_customer_address'
            ],
            'attribute_set_id' => $entityType->getDefaultAttributeSetId(),
            'attribute_group_id' => $attributeSet->getDefaultGroupId($entityType->getDefaultAttributeSetId()),
            'entity_type_id' => $entityType->getId(),
            'default_value' => '',
        ],
    ]
);
$select->setAttributeCode('test_text_attribute');
$select->save();

$customer = $objectManager
    ->create(Customer::class);
$customer->setWebsiteId(1)
    ->setEntityId(1)
    ->setEntityTypeId($entityType->getId())
    ->setAttributeSetId($entityType->getDefaultAttributeSetId())
    ->setEmail('JohnDoe@mail.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('John')
    ->setLastname('Doe')
    ->setGender(2)
    ->setTestTextAttribute('123');
$customer->isObjectNew(true);
// Create address
$address = $objectManager->create(Address::class);
//  default_billing and default_shipping information would not be saved, it is needed only for simple check
$address->addData(
    [
        'firstname' => 'Charles',
        'lastname' => 'Alston',
        'street' => '3781 Neuport Lane',
        'city' => 'Panola',
        'country_id' => 'US',
        'region_id' => '51',
        'postcode' => '30058',
        'telephone' => '770-322-3514',
        'default_billing' => 1,
        'default_shipping' => 1,
    ]
);
// Assign customer and address
$customer->addAddress($address);
$customer->save();
// Mark last address as default billing and default shipping for current customer
$customer->setDefaultBilling($address->getId());
$customer->setDefaultShipping($address->getId());
$customer->save();

$objectManager->get(Registry::class)->unregister('_fixture/Magento_ImportExport_Customer');
$objectManager->get(Registry::class)->register('_fixture/Magento_ImportExport_Customer', $customer);
