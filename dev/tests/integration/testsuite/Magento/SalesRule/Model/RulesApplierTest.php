<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\SalesRule\Test\Fixture\Rule as RuleFixture;
use Magento\SalesRule\Test\Fixture\RuleCoupon as CouponFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests discount descriptions with persisted coupons and multiple rules.
 */
class RulesApplierTest extends TestCase
{
    /**
     * @return void
     */
    #[DbIsolation(true)]
    #[DataFixture(RuleFixture::class, ['description' => 'Automatic promotion'], 'automatic_rule')]
    #[DataFixture(
        RuleFixture::class,
        ['coupon_type' => Rule::COUPON_TYPE_SPECIFIC, 'description' => 'Internal coupon description'],
        'coupon_rule'
    )]
    #[DataFixture(CouponFixture::class, ['rule_id' => '$coupon_rule.id$', 'code' => 'coupon-28432'], 'coupon')]
    public function testDiscountDescriptionsPreserveRuleLabels(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $fixtures = DataFixtureStorageManager::getStorage();
        $automaticRule = $fixtures->get('automatic_rule');
        $couponRule = $fixtures->get('coupon_rule');
        $couponCodes = [$fixtures->get('coupon')->getCode()];
        $address = $objectManager->create(Address::class);
        $address->setQuote($objectManager->create(Quote::class));
        $rulesApplier = $objectManager->create(RulesApplier::class);

        $rulesApplier->addDiscountDescription($address, $automaticRule, $couponCodes);
        $rulesApplier->addDiscountDescription($address, $couponRule, $couponCodes);

        self::assertSame(
            [$couponRule->getId() => 'coupon-28432'],
            $address->getDiscountDescriptionArray()
        );
        $validator = $objectManager->create(Validator::class);
        $validator->prepareDescription($address);
        self::assertSame('coupon-28432', $address->getDiscountDescription());

        $automaticRule->setStoreLabels([0 => 'Automatic label']);
        $address->setDiscountDescriptionArray([]);
        $rulesApplier->addDiscountDescription($address, $automaticRule, $couponCodes);
        $rulesApplier->addDiscountDescription($address, $couponRule, $couponCodes);
        $validator->prepareDescription($address);
        self::assertSame('Automatic label, coupon-28432', $address->getDiscountDescription());

        $couponRule->setStoreLabels([0 => 'Merchant label']);
        $rulesApplier->addDiscountDescription($address, $couponRule, $couponCodes);
        $validator->prepareDescription($address);
        self::assertSame('Automatic label, Merchant label', $address->getDiscountDescription());
    }
}
