<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Model\Quote\Address\Total;

/**
 * Shipping test.
 */
class ShippingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Api\GuestCartManagementInterface
     */
    private $cartManagement;

    /**
     * @var \Magento\Quote\Api\GuestCartItemRepositoryInterface
     */
    private $itemRepository;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->cartManagement = $this->objectManager->get(\Magento\Quote\Api\GuestCartManagementInterface::class);
        $this->itemRepository = $this->objectManager->get(\Magento\Quote\Api\GuestCartItemRepositoryInterface::class);
    }

    /**
     * Estimate shipment for product that match salesrule with free shipping.
     *
     * @magentoDataFixture Magento/SalesRule/_files/rule_free_shipping_by_product_weight.php
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     */
    public function testRuleByProductWeightWithFreeShipping()
    {
        $cartId = $this->prepareQuote(1);
        $methods = $this->estimateShipping($cartId);

        $this->assertTrue(count($methods) > 0);
        $this->assertEquals('flatrate', $methods[0]->getMethodCode());
        $this->assertEquals(0, $methods[0]->getAmount());
    }

    /**
     * Estimate shipment for product that doesn't match salesrule with free shipping.
     *
     * @magentoDataFixture Magento/SalesRule/_files/rule_free_shipping_by_product_weight.php
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     */
    public function testRuleByProductWeightWithoutFreeShipping()
    {
        $cartId = $this->prepareQuote(5);
        $methods = $this->estimateShipping($cartId);

        $this->assertTrue(count($methods) > 0);
        $this->assertEquals('flatrate', $methods[0]->getMethodCode());
        $this->assertEquals(25, $methods[0]->getAmount());
    }

    /**
     * Estimate shipment for guest cart.
     *
     * @param int $cartId
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
    private function estimateShipping($cartId)
    {
        $addressFactory = $this->objectManager->get(\Magento\Quote\Api\Data\AddressInterfaceFactory::class);
        /** @var \Magento\Quote\Api\Data\AddressInterface $address */
        $address = $addressFactory->create();
        $address->setCountryId('US');
        $address->setRegionId(2);

        /** @var \Magento\Quote\Api\GuestShipmentEstimationInterface $estimation */
        $estimation = $this->objectManager->get(\Magento\Quote\Api\GuestShipmentEstimationInterface::class);

        return $estimation->estimateByExtendedAddress($cartId, $address);
    }

    /**
     * Create guest quote with products.
     *
     * @param int $itemQty
     * @return int
     */
    private function prepareQuote($itemQty)
    {
        $cartId = $this->cartManagement->createEmptyCart();

        /** @var \Magento\Quote\Api\Data\CartItemInterfaceFactory $cartItemFactory */
        $cartItemFactory = $this->objectManager->get(\Magento\Quote\Api\Data\CartItemInterfaceFactory::class);

        /** @var \Magento\Quote\Api\Data\CartItemInterface $cartItem */
        $cartItem = $cartItemFactory->create();
        $cartItem->setQuoteId($cartId);
        $cartItem->setQty($itemQty);
        $cartItem->setSku('simple');
        $cartItem->setProductType(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);

        $this->itemRepository->save($cartItem);

        return $cartId;
    }
}
