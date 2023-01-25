<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Model\Quote\Address\Total;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\GuestCartItemRepositoryInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Api\GuestShipmentEstimationInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\SalesRule\Model\Rule\Condition\Combine;
use Magento\SalesRule\Model\Rule\Condition\Product;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingTest extends TestCase
{
    /**
     * @var GuestCartManagementInterface
     */
    private $cartManagement;

    /**
     * @var GuestCartItemRepositoryInterface
     */
    private $itemRepository;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->cartManagement = $this->objectManager->get(GuestCartManagementInterface::class);
        $this->itemRepository = $this->objectManager->get(GuestCartItemRepositoryInterface::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/SalesRule/_files/rule_free_shipping_by_product_weight.php
     * @magentoDataFixture Magento/Quote/_files/is_salable_product.php
     */
    public function testRuleByProductWeightWithFreeShipping()
    {
        $cartId = $this->cartManagement->createEmptyCart();
        $this->addToCart($cartId, 'simple-99', 1);
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
        $cartId = $this->cartManagement->createEmptyCart();
        $this->addToCart($cartId, 'simple-99', 5);
        $methods = $this->estimateShipping($cartId);

        $this->assertTrue(count($methods) > 0);
        $this->assertEquals('flatrate', $methods[0]->getMethodCode());
        $this->assertEquals(25, $methods[0]->getAmount());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/SalesRule/_files/cart_rule_free_shipping.php
     * @magentoDataFixture Magento/Catalog/_files/products_list.php
     */
    public function testFreeMethodWeight()
    {
        $this->setFreeShippingForProduct('simple-249');
        $cartId = $this->cartManagement->createEmptyCart();
        $this->addToCart($cartId, 'simple-249', 3);
        $this->addToCart($cartId, 'simple-156', 1);
        $this->estimateShipping($cartId);
        $quote = $this->getQuote($cartId);
        $this->assertEquals(10, $quote->getShippingAddress()->getFreeMethodWeight());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/SalesRule/_files/cart_rule_free_shipping.php
     * @magentoDataFixture Magento/Catalog/_files/products_list.php
     */
    public function testFreeMethodWeightWithMaximumQtyDiscount()
    {
        $this->setFreeShippingForProduct('simple-249', 2);
        $cartId = $this->cartManagement->createEmptyCart();
        $this->addToCart($cartId, 'simple-249', 5);
        $this->addToCart($cartId, 'simple-156', 1);
        $this->estimateShipping($cartId);
        $quote = $this->getQuote($cartId);
        $this->assertEquals(40, $quote->getShippingAddress()->getFreeMethodWeight());
    }

    /**
     * Estimate shipment for guest cart
     *
     * @param int $cartId
     * @return ShippingMethodInterface[]
     */
    private function estimateShipping($cartId)
    {
        $addressFactory = $this->objectManager->get(AddressInterfaceFactory::class);
        /** @var AddressInterface $address */
        $address = $addressFactory->create();
        $address->setCountryId('US');
        $address->setRegionId(2);

        /** @var GuestShipmentEstimationInterface $estimation */
        $estimation = $this->objectManager->get(GuestShipmentEstimationInterface::class);
        return $estimation->estimateByExtendedAddress($cartId, $address);
    }

    /**
     * @param string $cartMaskId
     * @return CartInterface
     * @throws NoSuchEntityException
     */
    private function getQuote(string $cartMaskId): CartInterface
    {
        /** @var CartRepositoryInterface $cartRepository */
        $cartRepository = $this->objectManager->get(CartRepositoryInterface::class);
        /** @var MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId */
        $maskedQuoteIdToQuoteId = $this->objectManager->get(MaskedQuoteIdToQuoteIdInterface::class);
        $cartId = $maskedQuoteIdToQuoteId->execute($cartMaskId);
        return $cartRepository->get($cartId);
    }

    /**
     * @param string $cartMaskId
     * @param string $sku
     * @param int $qty
     * @throws NoSuchEntityException
     */
    private function addToCart(string $cartMaskId, string $sku, int $qty): void
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        /** @var CartRepositoryInterface $cartRepository */
        $cartRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $product = $productRepository->get($sku);
        $quote = $this->getQuote($cartMaskId);
        $quote->addProduct($product, $qty);
        $cartRepository->save($quote);
    }

    /**
     * @param string $sku
     * @param int $qty
     */
    public function setFreeShippingForProduct(string $sku, int $qty = 0): void
    {
        /** @var Registry $registry */
        $registry = $this->objectManager->get(Registry::class);
        $salesRule = $registry->registry('cart_rule_free_shipping');
        $salesRule->setDiscountQty($qty);
        $data = [
            'actions' => [
                1 => [
                    'type' => Combine::class,
                    'attribute' => null,
                    'operator' => null,
                    'value' => '1',
                    'is_value_processed' => null,
                    'aggregator' => 'all',
                    'actions' => [
                        1 => [
                            'type' => Product::class,
                            'attribute' => 'sku',
                            'operator' => '==',
                            'value' => $sku,
                            'is_value_processed' => false,
                        ]
                    ]
                ]
            ],
        ];
        $salesRule->loadPost($data);
        $salesRule->save();
    }
}
