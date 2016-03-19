<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

class GuestShippingMethodManagementTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'quoteGuestShippingMethodManagementV1';
    const RESOURCE_PATH = '/V1/guest-carts/';

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

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $this->totalsCollector = $this->objectManager->create('Magento\Quote\Model\Quote\TotalsCollector');
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_and_address.php
     *
     */
    public function testGetListForVirtualCart()
    {
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $cartId = $quote->load('test_order_with_virtual_product', 'reserved_order_id')->getId();

        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Quote\Model\QuoteIdMaskFactory')
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();

        $this->assertEquals([], $this->_webApiCall($this->getListServiceInfo($cartId), ["cartId" => $cartId]));
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testGetList()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('test_order_1', 'reserved_order_id');
        $cartId = $quote->getId();
        if (!$cartId) {
            $this->fail('quote fixture failed');
        }

        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Quote\Model\QuoteIdMaskFactory')
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();

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
        $converter = $this->objectManager->create('\Magento\Quote\Model\Cart\ShippingMethodConverter');
        foreach ($groupedRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $result[] = $converter->modelToDataObject($rate, $currencyCode)->__toArray();
            }
        }
        return $result;
    }
}
