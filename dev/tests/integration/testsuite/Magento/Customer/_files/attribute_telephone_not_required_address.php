<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Model\Attribute;
use Magento\Eav\Model\AttributeRepository;

/** @var Attribute $model */
$attribute = Bootstrap::getObjectManager()->create(Attribute::class);
/** @var AttributeRepository $attributeRepository */
$attributeRepository = Bootstrap::getObjectManager()->create(AttributeRepository::class);
$attribute->loadByCode('customer_address', 'telephone');
$attribute->setIsRequired(false);
$attributeRepository->save($attribute);
