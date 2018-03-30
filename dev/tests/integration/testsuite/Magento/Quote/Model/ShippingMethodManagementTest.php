<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model;

/**
 * Class ShippingMethodManagementTest
 *
 * @magentoDbIsolation enabled
 */
class ShippingMethodManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoDataFixture Magento/SalesRule/_files/cart_rule_100_percent_off.php
     * @magentoDataFixture Magento/Sales/_files/quote_with_customer.php
     * @return void
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testRateAppliedToShipping()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository */
        $quoteRepository = $objectManager->create(\Magento\Quote\Api\CartRepositoryInterface::class);
        $customerQuote = $quoteRepository->getForCustomer(1);
        $this->assertEquals(0, $customerQuote->getBaseGrandTotal());
    }

    /**
     * @magentoConfigFixture current_store carriers/tablerate/active 1
     * @magentoConfigFixture current_store carriers/tablerate/condition_name package_qty
     * @magentoDataFixture Magento/SalesRule/_files/cart_rule_free_shipping.php
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/OfflineShipping/_files/tablerates.php
     * @return void
     */
    public function testEstimateByAddressWithCartPriceRuleByItem()
    {
        $this->executeTestFlow(0, 0);
    }

    /**
     * @magentoConfigFixture current_store carriers/tablerate/active 1
     * @magentoConfigFixture current_store carriers/tablerate/condition_name package_qty
     * @magentoDataFixture Magento/SalesRule/_files/cart_rule_free_shipping_by_cart.php
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/OfflineShipping/_files/tablerates.php
     * @return void
     */
    public function testEstimateByAddressWithCartPriceRuleByShipment()
    {
        // Rule applied to entire shipment should not overwrite flat or table rate shipping prices
        // Only rules applied to specific items should modify those prices (MAGETWO-63844)
        $this->executeTestFlow(5, 10);
    }

    /**
     * @magentoConfigFixture current_store carriers/tablerate/active 1
     * @magentoConfigFixture current_store carriers/tablerate/condition_name package_qty
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/OfflineShipping/_files/tablerates.php
     * @return void
     */
    public function testEstimateByAddress()
    {
        $this->executeTestFlow(5, 10);
    }

    /**
     * Provide testing of shipping method estimation based on address
     *
     * @param int $flatRateAmount
     * @param int $tableRateAmount
     * @return void
     */
    private function executeTestFlow($flatRateAmount, $tableRateAmount)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $objectManager->get(\Magento\Quote\Model\Quote::class);
        $quote->load('test01', 'reserved_order_id');
        $cartId = $quote->getId();
        if (!$cartId) {
            $this->fail('quote fixture failed');
        }
        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();
        $data = [
            'data' => [
                'country_id' => "US",
                'postcode' => null,
                'region' => null,
                'region_id' => null
            ]
        ];
        /** @var \Magento\Quote\Api\Data\EstimateAddressInterface $address */
        $address = $objectManager->create(\Magento\Quote\Api\Data\EstimateAddressInterface::class, $data);
        /** @var  \Magento\Quote\Api\GuestShippingMethodManagementInterface $shippingEstimation */
        $shippingEstimation = $objectManager->get(\Magento\Quote\Api\GuestShippingMethodManagementInterface::class);
        $result = $shippingEstimation->estimateByAddress($cartId, $address);
        $this->assertNotEmpty($result);
        $expectedResult = [
            'tablerate' => [
                    'method_code' => 'bestway',
                    'amount' => $tableRateAmount
                ],
            'flatrate' => [
                'method_code' => 'flatrate',
                'amount' => $flatRateAmount
            ]
        ];
        foreach ($result as $rate) {
            $this->assertEquals($expectedResult[$rate->getCarrierCode()]['amount'], $rate->getAmount());
            $this->assertEquals($expectedResult[$rate->getCarrierCode()]['method_code'], $rate->getMethodCode());
        }
    }
}
