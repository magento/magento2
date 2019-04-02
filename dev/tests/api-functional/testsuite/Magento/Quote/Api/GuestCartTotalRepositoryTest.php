<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

use Magento\Quote\Model\Cart\Totals;
use Magento\Quote\Model\Cart\Totals\Item as ItemTotals;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

class GuestCartTotalRepositoryTest extends WebapiAbstract
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

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->searchCriteriaBuilder = $this->objectManager->create(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        );
        $this->filterBuilder = $this->objectManager->create(
            \Magento\Framework\Api\FilterBuilder::class
        );
    }

    protected function getQuoteMaskedId($quoteId)
    {
        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->objectManager->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)->create();
        $quoteIdMask->load($quoteId, 'quote_id');
        return $quoteIdMask->getMaskedId();
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_shipping_method.php
     */
    public function testGetTotals()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test_order_1', 'reserved_order_id');
        $cartId = $this->getQuoteMaskedId($quote->getId());

        /** @var \Magento\Quote\Model\Quote\Address $shippingAddress */
        $shippingAddress = $quote->getShippingAddress();

        $data = [
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

        $requestData = ['cartId' => $cartId];

        $data = $this->formatTotalsData($data);

        $actual = $this->_webApiCall($this->getServiceInfoForTotalsService($cartId), $requestData);
        $actual = $this->formatTotalsData($actual);

        unset($actual['items'][0]['options']);
        unset($actual['weee_tax_applied_amount']);

        unset($actual['total_segments']);
        if (array_key_exists('extension_attributes', $actual)) {
            unset($actual['extension_attributes']);
        }

        $this->assertEquals($data, $actual);
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
                'service' => 'quoteGuestCartTotalRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'quoteGuestCartTotalRepositoryV1get',
            ],
            'rest' => [
                'resourcePath' => '/V1/guest-carts/' . $cartId . '/totals',
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
                $data[$key] = round($field, 1);
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
            ItemTotals::KEY_PRICE => $item->getPrice(),
            ItemTotals::KEY_BASE_PRICE => $item->getBasePrice(),
            ItemTotals::KEY_QTY => $item->getQty(),
            ItemTotals::KEY_ROW_TOTAL => $item->getRowTotal(),
            ItemTotals::KEY_BASE_ROW_TOTAL => $item->getBaseRowTotal(),
            ItemTotals::KEY_ROW_TOTAL_WITH_DISCOUNT => $item->getRowTotalWithDiscount(),
            ItemTotals::KEY_TAX_AMOUNT => $item->getTaxAmount(),
            ItemTotals::KEY_BASE_TAX_AMOUNT => $item->getBaseTaxAmount(),
            ItemTotals::KEY_TAX_PERCENT => $item->getTaxPercent(),
            ItemTotals::KEY_DISCOUNT_AMOUNT => $item->getDiscountAmount(),
            ItemTotals::KEY_BASE_DISCOUNT_AMOUNT => $item->getBaseDiscountAmount(),
            ItemTotals::KEY_DISCOUNT_PERCENT => $item->getDiscountPercent(),
            ItemTotals::KEY_PRICE_INCL_TAX => $item->getPriceInclTax(),
            ItemTotals::KEY_BASE_PRICE_INCL_TAX => $item->getBasePriceInclTax(),
            ItemTotals::KEY_ROW_TOTAL_INCL_TAX => $item->getRowTotalInclTax(),
            ItemTotals::KEY_BASE_ROW_TOTAL_INCL_TAX => $item->getBaseRowTotalInclTax(),
            ItemTotals::KEY_WEEE_TAX_APPLIED_AMOUNT => $item->getWeeeTaxAppliedAmount(),
            ItemTotals::KEY_WEEE_TAX_APPLIED => $item->getWeeeTaxApplied(),
            ItemTotals::KEY_NAME => $item->getName(),
        ];
    }
}
