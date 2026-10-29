<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Quote\Api;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\Virtual as VirtualProductFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteIdMaskFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GuestShippingMethodManagementTest extends WebapiAbstract
{
    public const SERVICE_VERSION = 'V1';
    public const SERVICE_NAME = 'quoteGuestShippingMethodManagementV1';
    public const RESOURCE_PATH = '/V1/guest-carts/';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector
     */
    protected $totalsCollector;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $this->totalsCollector = $this->objectManager->create(\Magento\Quote\Model\Quote\TotalsCollector::class);
    }

    /**
     * Test get list for virtual product cart (no shipping methods)
     */
    #[
        DataFixture(VirtualProductFixture::class, ['sku' => 'virtual-product-%uniqid%', 'price' => 10], 'product'),
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test_order_with_virtual_product'], 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(QuoteIdMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testGetListForVirtualCart()
    {
        $quoteIdMask = DataFixtureStorageManager::getStorage()->get('quoteIdMask');
        $cartId = $quoteIdMask->getMaskedId();

        $this->assertEquals([], $this->_webApiCall($this->getListServiceInfo($cartId), ["cartId" => $cartId]));
    }

    /**
     * Test get list of shipping methods for guest cart with address
     */
    #[
        DataFixture(ProductFixture::class, ['sku' => 'simple-shipping-%uniqid%', 'price' => 10], 'product'),
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test_order_1'], 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 2]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(QuoteIdMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testGetList()
    {
        $cart = DataFixtureStorageManager::getStorage()->get('cart');
        $quoteId = (int) $cart->getId();
        if (!$quoteId) {
            $this->fail('quote fixture failed');
        }

        $quoteIdMask = DataFixtureStorageManager::getStorage()->get('quoteIdMask');
        $cartId = $quoteIdMask->getMaskedId();

        /** @var \Magento\Quote\Api\CartRepositoryInterface $cartRepository */
        $cartRepository = $this->objectManager->get(\Magento\Quote\Api\CartRepositoryInterface::class);
        $quote = $cartRepository->get($quoteId);
        $quote->getShippingAddress()->collectShippingRates();
        $expectedRates = $quote->getShippingAddress()->getGroupedAllShippingRates();

        $expectedData = $this->convertRates($expectedRates, $quote->getQuoteCurrencyCode());

        $requestData = ["cartId" => $cartId];

        $returnedRates = $this->_webApiCall($this->getListServiceInfo($cartId), $requestData);
        $this->assertEquals($expectedData, $returnedRates);
    }

    /**
     * Service info
     *
     * @param int $cartId
     * @return array
     */
    protected function getListServiceInfo($cartId)
    {
        return [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/shipping-methods',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];
    }

    /**
     * Convert rate models array to data array
     *
     * @param string $currencyCode
     * @param \Magento\Quote\Model\Quote\Address\Rate[] $groupedRates
     * @return array
     */
    protected function convertRates($groupedRates, $currencyCode)
    {
        $result = [];
        /** @var \Magento\Quote\Model\Cart\ShippingMethodConverter $converter */
        $converter = $this->objectManager->create(\Magento\Quote\Model\Cart\ShippingMethodConverter::class);
        foreach ($groupedRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $result[] = $converter->modelToDataObject($rate, $currencyCode)->__toArray();
            }
        }
        return $result;
    }
}
