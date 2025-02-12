<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Cart\Totals;
use Magento\Quote\Model\Cart\Totals\Item as ItemTotals;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Tax\Test\Fixture\TaxRule as TaxRule;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\Config as Config;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\TestModuleOverrideConfig\Inheritance\Fixtures\FixturesInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartTotalRepositoryTest extends WebapiAbstract
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var FixturesInterface
     */
    private $fixtures;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->searchCriteriaBuilder = $this->objectManager->create(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        );
        $this->filterBuilder = $this->objectManager->create(
            \Magento\Framework\Api\FilterBuilder::class
        );
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_shipping_method.php
     */
    public function testGetTotals()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test_order_1', 'reserved_order_id');
        $cartId = $quote->getId();

        /** @var \Magento\Quote\Model\Quote\Address $shippingAddress */
        $shippingAddress = $quote->getShippingAddress();

        $data = $this->getData($quote, $shippingAddress);
        $data = $this->formatTotalsData($data);

        $requestData = ['cartId' => $cartId];

        $actual = $this->_webApiCall($this->getServiceInfoForTotalsService($cartId), $requestData);
        unset($actual['items'][0]['options']);
        unset($actual['weee_tax_applied_amount']);

        /** TODO: cover total segments with separate test */
        unset($actual['total_segments']);
        if (array_key_exists('extension_attributes', $actual)) {
            unset($actual['extension_attributes']);
        }
        $this->assertEquals($data, $actual);
    }

    #[
        Config('tax/defaults/region_id', '43'),
        Config('tax/defaults/postcode', '10036'),
        Config('shipping/origin/region_id', '43'),
        Config('tax/defaults/postcode', '10011'),
        DataFixture(
            TaxRule::class,
            [
                'tax_rate_ids' => [2],
                'product_tax_class_ids' => [2],
                'customer_tax_class_ids' => [3]
            ],
            'tax_rule'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'price' => 5
            ],
            'product'
        ),
        DataFixture(
            GuestCartFixture::class,
            as: 'cart'
        ),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$product.id$'
            ]
        ),
        DataFixture(
            SetBillingAddressFixture::class,
            [
                'cart_id' => '$cart.id$',
                'address' => [
                    AddressInterface::KEY_POSTCODE => 10036,
                    AddressInterface::KEY_CITY => 'New York',
                    AddressInterface::KEY_REGION_ID => 43
                ]
            ]
        ),
        DataFixture(
            SetShippingAddressFixture::class,
            [
                'cart_id' => '$cart.id$',
                'address' => [
                    AddressInterface::KEY_POSTCODE => 10036,
                    AddressInterface::KEY_CITY => 'New York',
                    AddressInterface::KEY_REGION_ID => 43
                ]
            ]
        ),
        DataFixture(
            SetGuestEmailFixture::class,
            [
                'cart_id' => '$cart.id$'
            ]
        ),
        DataFixture(
            SetDeliveryMethodFixture::class,
            [
                'cart_id' => '$cart.id$'
            ]
        ),
        DataFixture(
            SetPaymentMethodFixture::class,
            [
                'cart_id' => '$cart.id$'
            ]
        ),
    ]
    public function testGetGrandTotalsWithIncludedTaxAndSameCurrency()
    {
        $cart = $this->fixtures->get('cart');
        $cartId = $cart->getid();
        $requestData = ['cartId' => $cartId];
        $actual = $this->_webApiCall($this->getServiceInfoForTotalsService($cartId), $requestData);
        $this->assertEquals($actual['base_grand_total'], $actual['grand_total']);
    }

    /**
     */
    public function testGetTotalsWithAbsentQuote()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No such entity');

        $cartId = 9999999999;
        $requestData = ['cartId' => $cartId];
        $this->_webApiCall($this->getServiceInfoForTotalsService($cartId), $requestData);
    }

    /**
     * Get service info for totals service
     *
     * @param string $cartId
     * @return array
     */
    protected function getServiceInfoForTotalsService($cartId)
    {
        return [
            'soap' => [
                'service' => 'quoteCartTotalRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'quoteCartTotalRepositoryV1get',
            ],
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId . '/totals',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
        ];
    }

    /**
     * Adjust response details for SOAP protocol
     *
     * @param array $data
     * @return array
     */
    protected function formatTotalsData($data)
    {
        foreach ($data as $key => $field) {
            if (is_numeric($field)) {
                $data[$key] = round((float) $field, 1);
                if ($data[$key] === null) {
                    $data[$key] = 0.0;
                }
            }
        }

        unset($data[Totals::KEY_BASE_SUBTOTAL_INCL_TAX]);

        return $data;
    }

    /**
     * Fetch quote item totals data from quote
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return array
     */
    protected function getQuoteItemTotalsData(\Magento\Quote\Model\Quote $quote)
    {
        $items = $quote->getAllItems();
        $item = array_shift($items);
        return [
            ItemTotals::KEY_ITEM_ID => $item->getItemId(),
            ItemTotals::KEY_PRICE => (int)$item->getPrice(),
            ItemTotals::KEY_BASE_PRICE => (int)$item->getBasePrice(),
            ItemTotals::KEY_QTY => $item->getQty(),
            ItemTotals::KEY_ROW_TOTAL => (int)$item->getRowTotal(),
            ItemTotals::KEY_BASE_ROW_TOTAL => (int)$item->getBaseRowTotal(),
            ItemTotals::KEY_ROW_TOTAL_WITH_DISCOUNT => (int)$item->getRowTotalWithDiscount(),
            ItemTotals::KEY_TAX_AMOUNT => (int)$item->getTaxAmount(),
            ItemTotals::KEY_BASE_TAX_AMOUNT => (int)$item->getBaseTaxAmount(),
            ItemTotals::KEY_TAX_PERCENT => (int)$item->getTaxPercent(),
            ItemTotals::KEY_DISCOUNT_AMOUNT => (int)$item->getDiscountAmount(),
            ItemTotals::KEY_BASE_DISCOUNT_AMOUNT => (int)$item->getBaseDiscountAmount(),
            ItemTotals::KEY_DISCOUNT_PERCENT => (int)$item->getDiscountPercent(),
            ItemTotals::KEY_PRICE_INCL_TAX => (int)$item->getPriceInclTax(),
            ItemTotals::KEY_BASE_PRICE_INCL_TAX => (int)$item->getBasePriceInclTax(),
            ItemTotals::KEY_ROW_TOTAL_INCL_TAX => (int)$item->getRowTotalInclTax(),
            ItemTotals::KEY_BASE_ROW_TOTAL_INCL_TAX => (int)$item->getBaseRowTotalInclTax(),
            ItemTotals::KEY_WEEE_TAX_APPLIED_AMOUNT => $item->getWeeeTaxAppliedAmount(),
            ItemTotals::KEY_WEEE_TAX_APPLIED => $item->getWeeeTaxApplied(),
            ItemTotals::KEY_NAME => $item->getName(),
        ];
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_shipping_method.php
     */
    public function testGetMyTotals()
    {
        $this->_markTestAsRestOnly();

        // get customer ID token
        /** @var \Magento\Integration\Api\CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(
            \Magento\Integration\Api\CustomerTokenServiceInterface::class
        );
        $token = $customerTokenService->createCustomerAccessToken('customer@example.com', 'password');

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test_order_1', 'reserved_order_id');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/mine/totals',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
                'token' => $token
            ],
        ];

        /** @var \Magento\Quote\Model\Quote\Address $shippingAddress */
        $shippingAddress = $quote->getShippingAddress();

        $data = $this->getData($quote, $shippingAddress);
        $data = $this->formatTotalsData($data);

        $actual = $this->_webApiCall($serviceInfo);
        unset($actual['items'][0]['options']);
        unset($actual['weee_tax_applied_amount']);

        /** TODO: cover total segments with separate test */
        unset($actual['total_segments']);
        if (array_key_exists('extension_attributes', $actual)) {
            unset($actual['extension_attributes']);
        }
        $this->assertEquals($data, $actual);
    }

    /**
     * Get expected data.
     *
     * @param Quote $quote
     * @param Address $shippingAddress
     *
     * @return array
     */
    private function getData(Quote $quote, Address $shippingAddress) : array
    {
        return [
            Totals::KEY_GRAND_TOTAL => $quote->getGrandTotal(),
            Totals::KEY_BASE_GRAND_TOTAL => $quote->getBaseGrandTotal(),
            Totals::KEY_SUBTOTAL => $quote->getSubtotal(),
            Totals::KEY_BASE_SUBTOTAL => $quote->getBaseSubtotal(),
            Totals::KEY_DISCOUNT_AMOUNT => $shippingAddress->getDiscountAmount(),
            Totals::KEY_BASE_DISCOUNT_AMOUNT => $shippingAddress->getBaseDiscountAmount(),
            Totals::KEY_SUBTOTAL_WITH_DISCOUNT => $quote->getSubtotalWithDiscount(),
            Totals::KEY_BASE_SUBTOTAL_WITH_DISCOUNT => $quote->getBaseSubtotalWithDiscount(),
            Totals::KEY_SHIPPING_AMOUNT => $shippingAddress->getShippingAmount(),
            Totals::KEY_BASE_SHIPPING_AMOUNT => $shippingAddress->getBaseShippingAmount(),
            Totals::KEY_SHIPPING_DISCOUNT_AMOUNT => $shippingAddress->getShippingDiscountAmount(),
            Totals::KEY_BASE_SHIPPING_DISCOUNT_AMOUNT => $shippingAddress->getBaseShippingDiscountAmount(),
            Totals::KEY_TAX_AMOUNT => $shippingAddress->getTaxAmount(),
            Totals::KEY_BASE_TAX_AMOUNT => $shippingAddress->getBaseTaxAmount(),
            Totals::KEY_SHIPPING_TAX_AMOUNT => $shippingAddress->getShippingTaxAmount(),
            Totals::KEY_BASE_SHIPPING_TAX_AMOUNT => $shippingAddress->getBaseShippingTaxAmount(),
            Totals::KEY_SUBTOTAL_INCL_TAX => $shippingAddress->getSubtotalInclTax(),
            Totals::KEY_BASE_SUBTOTAL_INCL_TAX => $shippingAddress->getBaseSubtotalTotalInclTax(),
            Totals::KEY_SHIPPING_INCL_TAX => $shippingAddress->getShippingInclTax(),
            Totals::KEY_BASE_SHIPPING_INCL_TAX => $shippingAddress->getBaseShippingInclTax(),
            Totals::KEY_BASE_CURRENCY_CODE => $quote->getBaseCurrencyCode(),
            Totals::KEY_QUOTE_CURRENCY_CODE => $quote->getQuoteCurrencyCode(),
            Totals::KEY_ITEMS_QTY => $quote->getItemsQty(),
            Totals::KEY_ITEMS => [$this->getQuoteItemTotalsData($quote)],
        ];
    }
}
