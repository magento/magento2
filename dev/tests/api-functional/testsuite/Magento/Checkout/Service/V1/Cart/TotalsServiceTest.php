<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Cart;

use Magento\Checkout\Service\V1\Data\Cart\Totals;
use Magento\Checkout\Service\V1\Data\Cart\Totals\Item as ItemTotals;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config as RestConfig;

class TotalsServiceTest extends WebapiAbstract
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->searchBuilder = $this->objectManager->create(
            'Magento\Framework\Api\SearchCriteriaBuilder'
        );
        $this->filterBuilder = $this->objectManager->create(
            'Magento\Framework\Api\FilterBuilder'
        );
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_shipping_method.php
     */
    public function testGetTotals()
    {
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->objectManager->create('Magento\Sales\Model\Quote');
        $quote->load('test_order_1', 'reserved_order_id');
        $cartId = $quote->getId();

        /** @var \Magento\Sales\Model\Quote\Address $shippingAddress */
        $shippingAddress = $quote->getShippingAddress();

        $data = [
            Totals::BASE_GRAND_TOTAL => $quote->getBaseGrandTotal(),
            Totals::GRAND_TOTAL => $quote->getGrandTotal(),
            Totals::BASE_SUBTOTAL => $quote->getBaseSubtotal(),
            Totals::SUBTOTAL => $quote->getSubtotal(),
            Totals::BASE_SUBTOTAL_WITH_DISCOUNT => $quote->getBaseSubtotalWithDiscount(),
            Totals::SUBTOTAL_WITH_DISCOUNT => $quote->getSubtotalWithDiscount(),
            Totals::DISCOUNT_AMOUNT => $shippingAddress->getDiscountAmount(),
            Totals::BASE_DISCOUNT_AMOUNT => $shippingAddress->getBaseDiscountAmount(),
            Totals::SHIPPING_AMOUNT => $shippingAddress->getShippingAmount(),
            Totals::BASE_SHIPPING_AMOUNT => $shippingAddress->getBaseShippingAmount(),
            Totals::SHIPPING_DISCOUNT_AMOUNT => $shippingAddress->getShippingDiscountAmount(),
            Totals::BASE_SHIPPING_DISCOUNT_AMOUNT => $shippingAddress->getBaseShippingDiscountAmount(),
            Totals::TAX_AMOUNT => $shippingAddress->getTaxAmount(),
            Totals::BASE_TAX_AMOUNT => $shippingAddress->getBaseTaxAmount(),
            Totals::SHIPPING_TAX_AMOUNT => $shippingAddress->getShippingTaxAmount(),
            Totals::BASE_SHIPPING_TAX_AMOUNT => $shippingAddress->getBaseShippingTaxAmount(),
            Totals::SUBTOTAL_INCL_TAX => $shippingAddress->getSubtotalInclTax(),
            Totals::BASE_SUBTOTAL_INCL_TAX => $shippingAddress->getBaseSubtotalTotalInclTax(),
            Totals::SHIPPING_INCL_TAX => $shippingAddress->getShippingInclTax(),
            Totals::BASE_SHIPPING_INCL_TAX => $shippingAddress->getBaseShippingInclTax(),
            Totals::BASE_CURRENCY_CODE => $quote->getBaseCurrencyCode(),
            Totals::QUOTE_CURRENCY_CODE => $quote->getQuoteCurrencyCode(),
            Totals::ITEMS => [$this->getQuoteItemTotalsData($quote)],
        ];

        $requestData = ['cartId' => $cartId];

        $data = $this->formatTotalsData($data);

        $this->assertEquals($data, $this->_webApiCall($this->getServiceInfoForTotalsService($cartId), $requestData));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No such entity
     */
    public function testGetTotalsWithAbsentQuote()
    {
        $cartId = 'unknownCart';
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
                'service' => 'checkoutCartTotalsServiceV1',
                'serviceVersion' => 'V1',
                'operation' => 'checkoutCartTotalsServiceV1GetTotals',
            ],
            'rest' => [
                'resourcePath' => '/V1/carts/' . $cartId . '/totals',
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
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
                $data[$key] = round($field, 1);
                if ($data[$key] === null) {
                    $data[$key] = 0.0;
                }
            }
        }

        unset($data[Totals::BASE_SUBTOTAL_INCL_TAX]);

        return $data;
    }

    /**
     * Fetch quote item totals data from quote
     *
     * @param \Magento\Sales\Model\Quote $quote
     * @return array
     */
    protected function getQuoteItemTotalsData(\Magento\Sales\Model\Quote $quote)
    {
        $items = $quote->getAllItems();
        $item = array_shift($items);

        return [
            ItemTotals::PRICE => $item->getPrice(),
            ItemTotals::BASE_PRICE => $item->getBasePrice(),
            ItemTotals::QTY => $item->getQty(),
            ItemTotals::ROW_TOTAL => $item->getRowTotal(),
            ItemTotals::BASE_ROW_TOTAL => $item->getBaseRowTotal(),
            ItemTotals::ROW_TOTAL_WITH_DISCOUNT => $item->getRowTotalWithDiscount(),
            ItemTotals::TAX_AMOUNT => $item->getTaxAmount(),
            ItemTotals::BASE_TAX_AMOUNT => $item->getBaseTaxAmount(),
            ItemTotals::TAX_PERCENT => $item->getTaxPercent(),
            ItemTotals::DISCOUNT_AMOUNT => $item->getDiscountAmount(),
            ItemTotals::BASE_DISCOUNT_AMOUNT => $item->getBaseDiscountAmount(),
            ItemTotals::DISCOUNT_PERCENT => $item->getDiscountPercent(),
            ItemTotals::PRICE_INCL_TAX => $item->getPriceInclTax(),
            ItemTotals::BASE_PRICE_INCL_TAX => $item->getBasePriceInclTax(),
            ItemTotals::ROW_TOTAL_INCL_TAX => $item->getRowTotalInclTax(),
            ItemTotals::BASE_ROW_TOTAL_INCL_TAX => $item->getBaseRowTotalInclTax(),
        ];
    }
}
