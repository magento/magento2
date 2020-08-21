<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Tests disabled cart rules for guest's cart
 */
class GuestTotalsInformationManagement extends WebapiAbstract
{
    private const SERVICE_OPERATION = 'calculate';
    private const SERVICE_NAME = 'checkoutGuestTotalsInformationManagementV1';
    private const SERVICE_VERSION = 'V1';
    private const RESOURCE_PATH = '/V1/guest-carts/:cartId/totals-information';
    private const QUOTE_RESERVED_ORDER_ID = 'test01';
    private const SALES_RULE_ID = 'Magento/SalesRule/_files/cart_rule_50_percent_off_no_condition/salesRuleId';

    /**
     * Test sales rule changes should be persisted in the database
     *
     * @magentoApiDataFixture Magento/SalesRule/_files/cart_rule_50_percent_off_no_condition.php
     * @magentoApiDataFixture Magento/Sales/_files/quote.php
     */
    public function testCalculate()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        /** @var \Magento\SalesRule\Model\Rule $salesRule */
        /** @var \Magento\Framework\Registry $registry */
        $registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
        $quote = Bootstrap::getObjectManager()->get(\Magento\Quote\Model\QuoteFactory::class)->create();
        $quote->load(self::QUOTE_RESERVED_ORDER_ID, 'reserved_order_id');
        $quoteIdMask = Bootstrap::getObjectManager()->get(\Magento\Quote\Model\QuoteIdMaskFactory::class)->create();
        $quoteIdMask->load($quote->getId(), 'quote_id');
        $salesRuleId = $registry->registry(self::SALES_RULE_ID);
        $salesRule = Bootstrap::getObjectManager()->create(\Magento\SalesRule\Model\RuleFactory::class)->create();
        $salesRule->load($salesRuleId);
        $this->assertContains($salesRule->getRuleId(), str_getcsv($quote->getAppliedRuleIds()));
        $salesRule->setIsActive(0);
        $salesRule->save();
        $response = $this->_webApiCall(
            [
                'rest' => [
                    'resourcePath' => str_replace(':cartId', $quoteIdMask->getMaskedId(), self::RESOURCE_PATH),
                    'httpMethod' => Request::HTTP_METHOD_POST,
                ],
                'soap' => [
                    'service' => self::SERVICE_NAME,
                    'serviceVersion' => self::SERVICE_VERSION,
                    'operation' => self::SERVICE_NAME . self::SERVICE_OPERATION,
                ],
            ],
            [
                'addressInformation' => [
                    'address' => []
                ]
            ]
        );
        $this->assertNotEmpty($response);
        $quote->load(self::QUOTE_RESERVED_ORDER_ID, 'reserved_order_id');
        $this->assertNotContains($salesRule->getId(), str_getcsv($quote->getAppliedRuleIds()));
    }
}
