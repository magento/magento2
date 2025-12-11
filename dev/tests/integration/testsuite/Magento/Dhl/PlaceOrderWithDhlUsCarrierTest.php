<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Dhl;

use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Test\Fixture\AddProductToCart as AddBundleProductToCart;
use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\ConfigurableProduct\Test\Fixture\AddProductToCart as AddConfigurableProductToCartFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Dhl\Model\Carrier;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\AsyncClient\Response;
use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test to verify order placement using dhl international shipping carrier
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 */
class PlaceOrderWithDhlUsCarrierTest extends TestCase
{
    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $quoteRepository;

    /**
     * @var CartManagementInterface
     */
    private CartManagementInterface $cartManagement;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var AsyncClientInterface
     */
    private AsyncClientInterface $httpClient;

    /**
     * @var string|null
     */
    private ?string $selectedShippingMethod = null;

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $this->cartManagement = $this->objectManager->get(CartManagementInterface::class);
        $this->orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $this->httpClient = $this->objectManager->get(AsyncClientInterface::class);
    }

    #[
        Config('payment/checkmo/active', '1', 'store', 'default'),
        // Shipping origin: US
        Config('shipping/origin/country_id', 'US', 'store', 'default'),
        Config('shipping/origin/region_id', '12', 'store', 'default'),
        Config('shipping/origin/postcode', '90034', 'store', 'default'),
        Config('shipping/origin/city', 'los angeles', 'store', 'default'),
        Config('shipping/origin/street_line1', '123 Warehouse Ave', 'store', 'default'),
        // DHL carrier configuration (US) with fake credentials and REST gateway
        Config('carriers/dhl/active', '1', 'store', 'default'),
        Config('carriers/dhl/type', 'DHL_REST', 'store', 'default'),
        Config('carriers/dhl/gateway_rest_url', 'https://express.api.dhl.com/mydhlapi', 'store', 'default'),
        Config('carriers/dhl/id', 'some ID', 'store', 'default'),
        Config('carriers/dhl/password', 'some password', 'store', 'default'),
        Config('carriers/dhl/api_key', 'some KEY', 'store', 'default'),
        Config('carriers/dhl/api_secret', 'some secret', 'store', 'default'),
        Config('carriers/dhl/account', '998765432', 'store', 'default'),
        Config('carriers/dhl/sandbox_mode', '1', 'store', 'default'),
        // Store information matching shipping origin
        Config('general/store_information/name', 'store', 'store', 'default'),
        Config('general/store_information/phone', '1234567890', 'store', 'default'),
        Config('general/store_information/country_id', 'US', 'store', 'default'),
        Config('general/store_information/region_id', '12', 'store', 'default'),
        Config('general/store_information/postcode', '90034', 'store', 'default'),
        Config('general/store_information/city', 'los angeles', 'store', 'default'),
        Config('general/store_information/street_line1', '123 Warehouse Ave', 'store', 'default'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(ProductFixture::class, ['price' => 10], as: 'p1'),
        DataFixture(ProductFixture::class, ['price' => 20], as: 'p2'),
        DataFixture(AttributeFixture::class, as: 'attr'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attr$'], '_links' => ['$p1$', '$p2$']],
            'cp1'
        ),
        DataFixture(ProductFixture::class, ['price' => 20], 'product1'),
        DataFixture(ProductFixture::class, ['price' => 10], 'product2'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product1.sku$'], 'selection1'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product2.sku$'], 'selection2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection1$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection2$']], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-fixed-price',
                'price_type' => Price::PRICE_TYPE_DYNAMIC,
                '_options' => ['$opt1$', '$opt2$']
            ],
            'bundle_product_1'
        ),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]
        ),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$cp1.id$',
                'child_product_id' => '$p2.id$',
                'qty' => 1
            ]
        ),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundle_product_1.id$',
                'selections' => [['$product1.id$'], ['$product2.id$']]
            ]
        ),
    ]
    /**
     * Verifies successful order placement using DHL-US shipping carrier
     *
     * @return void
     */
    public function testPlaceOrderWithDhlUsCarrier(): void
    {
        $cartId = (int)$this->fixtures->get('cart')->getId();
        $this->setShippingAndBillingAddressForQuote($cartId);
        $content = file_get_contents(__DIR__ . '/_files/dhl_quote_response.json');
        $response = new Response(200, [], $content);
        $this->httpClient->nextResponses(array_fill(0, Carrier::UNAVAILABLE_DATE_LOOK_FORWARD + 1, $response));
        $order = $this->orderRepository->get($this->selectDhlAndCheckmoAndPlaceOrder($cartId));
        $this->assertNotEmpty($order->getIncrementId());
        $this->assertSame($this->selectedShippingMethod, $order->getShippingMethod());
    }

    /**
     * Set billing and shipping address for card
     *
     * @param int $cartId
     * @return void
     */
    private function setShippingAndBillingAddressForQuote(int $cartId): void
    {
        $quote = $this->quoteRepository->get($cartId);
        /** @var AddressInterface $address */
        $address = $this->objectManager->create(AddressInterface::class);
        $address->setFirstname('Joe')
            ->setLastname('Doe')
            ->setCountryId('CA')
            ->setRegionId(76)
            ->setRegion('Quebec')
            ->setCity('Sherbrooke')
            ->setStreet('3197 rue Parc')
            ->setPostcode('J1L 1C9')
            ->setTelephone('9876543210');
        $quote->setBillingAddress($address);
        $quote->setShippingAddress($address);
        $this->quoteRepository->save($quote);
    }

    /**
     * Set dhl any international shipping method for quote and place order
     *
     * @param int $cartId
     * @return int
     */
    private function selectDhlAndCheckmoAndPlaceOrder(int $cartId): int
    {
        $quote = $this->quoteRepository->get($cartId);
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true);
        $quote->collectTotals();
        foreach ($shippingAddress->getAllShippingRates() as $rate) {
            if ($rate->getCarrier() === 'dhl') {
                $methodCode = (string)$rate->getMethod();
                $this->selectedShippingMethod = 'dhl_' . $methodCode;
                break;
            }
        }
        if ($this->selectedShippingMethod === null) {
            $this->fail('No DHL shipping rates available to select.');
        }
        $shippingAddress->setShippingMethod($this->selectedShippingMethod)->setCollectShippingRates(false);
        $quote->getPayment()->setMethod('checkmo');
        $this->quoteRepository->save($quote);
        return (int)$this->cartManagement->placeOrder($quote->getId());
    }
}
