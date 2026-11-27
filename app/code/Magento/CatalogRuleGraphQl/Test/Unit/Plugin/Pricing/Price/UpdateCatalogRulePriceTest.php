<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRuleGraphQl\Test\Unit\Plugin\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\CatalogRule\Pricing\Price\CatalogRulePrice;
use Magento\CatalogRuleGraphQl\Plugin\Pricing\Price\UpdateCatalogRulePrice;
use Magento\Customer\Model\Group;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdateCatalogRulePriceTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $dateTime;

    /**
     * @var Rule|MockObject
     */
    private $ruleResource;

    /**
     * @var CatalogRulePrice|MockObject
     */
    private $catalogRulePrice;

    /**
     * @var UpdateCatalogRulePrice
     */
    private $plugin;

    protected function setUp(): void
    {
        $this->dateTime = $this->createMock(TimezoneInterface::class);
        $this->ruleResource = $this->createMock(Rule::class);
        $this->catalogRulePrice = $this->createMock(CatalogRulePrice::class);

        $objectManager = new ObjectManager($this);
        $this->plugin = $objectManager->getObject(
            UpdateCatalogRulePrice::class,
            [
                'dateTime' => $this->dateTime,
                'ruleResource' => $this->ruleResource,
            ]
        );
    }

    /**
     * Catalog rule price must be resolved for the NOT LOGGED IN customer group (id 0).
     *
     * The customer group id 0 is a valid group ("NOT LOGGED IN") used for guest storefront
     * requests over GraphQL, so it must not be treated as an absent group.
     *
     * @return void
     */
    public function testAfterGetValueResolvesRulePriceForNotLoggedInGroup(): void
    {
        $rulePrice = 42.0;
        $product = $this->createProductMock(Group::NOT_LOGGED_IN_ID);
        $this->catalogRulePrice->method('getProduct')->willReturn($product);

        $this->dateTime->method('scopeDate')->willReturn('2025-01-01 00:00:00');
        $this->ruleResource->expects($this->once())
            ->method('getRulePrice')
            ->willReturn($rulePrice);

        $this->assertSame(
            $rulePrice,
            $this->plugin->afterGetValue($this->catalogRulePrice, false)
        );
    }

    /**
     * Catalog rule price must be resolved for a logged in customer group.
     *
     * @return void
     */
    public function testAfterGetValueResolvesRulePriceForLoggedInGroup(): void
    {
        $rulePrice = 15.5;
        $product = $this->createProductMock(1);
        $this->catalogRulePrice->method('getProduct')->willReturn($product);

        $this->dateTime->method('scopeDate')->willReturn('2025-01-01 00:00:00');
        $this->ruleResource->expects($this->once())
            ->method('getRulePrice')
            ->willReturn($rulePrice);

        $this->assertSame(
            $rulePrice,
            $this->plugin->afterGetValue($this->catalogRulePrice, false)
        );
    }

    /**
     * The original value must be returned unchanged when no customer group is set on the product.
     *
     * @return void
     */
    public function testAfterGetValueSkipsWhenCustomerGroupIsNotSet(): void
    {
        $product = $this->createPartialMockWithReflection(
            Product::class,
            ['getCustomerGroupId', 'getStore', 'getId']
        );
        $product->method('getCustomerGroupId')->willReturn(null);
        $this->catalogRulePrice->method('getProduct')->willReturn($product);

        $this->ruleResource->expects($this->never())
            ->method('getRulePrice');

        $this->assertFalse(
            $this->plugin->afterGetValue($this->catalogRulePrice, false)
        );
    }

    /**
     * The original value must be returned unchanged when no product is available.
     *
     * @return void
     */
    public function testAfterGetValueSkipsWhenProductIsMissing(): void
    {
        $this->catalogRulePrice->method('getProduct')->willReturn(null);

        $this->ruleResource->expects($this->never())
            ->method('getRulePrice');

        $this->assertSame(
            9.99,
            $this->plugin->afterGetValue($this->catalogRulePrice, 9.99)
        );
    }

    /**
     * Create a product mock configured with the given customer group id and a store.
     *
     * @param int $customerGroupId
     * @return Product|MockObject
     */
    private function createProductMock(int $customerGroupId)
    {
        $store = $this->createMock(Store::class);
        $store->method('getId')->willReturn(1);
        $store->method('getWebsiteId')->willReturn(1);

        $product = $this->createPartialMockWithReflection(
            Product::class,
            ['getCustomerGroupId', 'getStore', 'getId']
        );
        $product->method('getCustomerGroupId')->willReturn($customerGroupId);
        $product->method('getStore')->willReturn($store);
        $product->method('getId')->willReturn(100);

        return $product;
    }
}
