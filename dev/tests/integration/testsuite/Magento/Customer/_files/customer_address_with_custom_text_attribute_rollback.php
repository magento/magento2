<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Model\Customer;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Model\Attribute;

/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $attribute Attribute */
$attribute = Bootstrap::getObjectManager()->create(
    Attribute::class
);
$attribute->loadByCode('customer', 'test_text_attribute');
$attribute->delete();

/** @var Customer $customer */
$customer = Bootstrap::getObjectManager()
    ->create(Customer::class);
$customer->setWebsiteId(1);
$customer->loadByEmail('JohnDoe@mail.com');
$customer->delete();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
