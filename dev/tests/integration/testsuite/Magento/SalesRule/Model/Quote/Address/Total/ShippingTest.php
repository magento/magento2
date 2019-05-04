<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Model\Quote\Address\Total;

class ShippingTest extends \PHPUnit\Framework\TestCase
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
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/SalesRule/_files/rule_free_shipping_by_product_weight.php
     * @magentoDataFixture Magento/Quote/_files/is_salable_product.php
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
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/SalesRule/_files/rule_free_shipping_by_product_weight.php
     * @magentoDataFixture Magento/Quote/_files/is_salable_product.php
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
     * Estimate shipment for guest cart
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
     * Create guest quote with products
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
        $cartItem->setSku('simple-99');
        $cartItem->setProductType(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);

        $this->itemRepository->save($cartItem);

        return $cartId;
    }
}
