<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Quote\Model\QuoteIdMask;

/**
 * Class GuestCollectRatesTest checks that totals will be recollected properly with new shipping method
 */
class GuestCollectRatesTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'quoteGuestCartTotalManagementV1';
    const RESOURCE_PATH = '/V1/guest-carts/';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Quote
     */
    protected $quote;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->quote = $this->objectManager->create(Quote::class);
    }

    /**
     * Checks that totals are properly recollected after changing shipping method
     *
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_shipping_method.php
     */
    public function testCollectRatesWithChangedShippingMethod()
    {
        $this->quote->load('test_order_1', 'reserved_order_id');
        $cartId = $this->quote->getId();

        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->objectManager
            ->create(QuoteIdMaskFactory::class)
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');

        $cartId = $quoteIdMask->getMaskedId();
        $requestData = [
            "shippingMethodCode" => "freeshipping",
            "shippingCarrierCode" => "freeshipping",
            "paymentMethod" => [
                "method" => "checkmo",
            ],
        ];

        if (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
            $requestData['cartId'] = $cartId;
        }

        $totals = $this->_webApiCall($this->getListServiceInfo($cartId), $requestData);
        $this->assertEquals(20, $totals['grand_total']);
        $this->assertEquals(0, $totals['shipping_amount']);

        $requestData['shippingMethodCode'] = 'flatrate';
        $requestData['shippingCarrierCode'] = 'flatrate';

        $totals = $this->_webApiCall($this->getListServiceInfo($cartId), $requestData);
        $this->assertEquals(30, $totals['grand_total']);
        $this->assertEquals(10, $totals['shipping_amount']);
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
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/collect-totals',
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'quoteGuestCartTotalManagementV1CollectTotals',
            ],
        ];
    }
}
