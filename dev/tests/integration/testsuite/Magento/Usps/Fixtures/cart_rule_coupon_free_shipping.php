<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Registry;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\ResourceModel\Rule as RuleResource;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RuleFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/SalesRule/_files/cart_rule_free_shipping.php');

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);
$salesRule = $registry->registry('cart_rule_free_shipping');
$salesRule->setCouponType(Rule::COUPON_TYPE_SPECIFIC)->setUseAutoGeneration(0);
$salesRule->save();
$couponCode = 'IMPHBR852R61';
$coupon = $objectManager->create(Coupon::class);
$coupon->setRuleId($salesRule->getId())
    ->setCode($couponCode)
    ->setType(0);
$objectManager->get(CouponRepositoryInterface::class)
    ->save($coupon);

$registry->unregister('cart_rule_free_shipping');
$registry->register('cart_rule_free_shipping', $salesRule);
