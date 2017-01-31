<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

use Magento\Catalog\Model\Product\Type;
use Magento\TestFramework\Helper\Bootstrap;

class QuoteManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Create order with product that has child items
     *
     * @magentoDataFixture Magento/Sales/_files/quote_with_bundle.php
     */
    public function testSubmit()
    {
        $this->markTestSkipped('MAGETWO-50989');
        /**
         * Preconditions:
         * Load quote with Bundle product that has at least to child products
         */
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $objectManager->create('\Magento\Quote\Model\Quote');
        $quote->load('test01', 'reserved_order_id');

        /** Execute SUT */
        /** @var \Magento\Quote\Api\CartManagementInterface $model */
        $model = $objectManager->create('\Magento\Quote\Api\CartManagementInterface');
        $order = $model->submit($quote);

        /** Check if SUT caused expected effects */
        $orderItems = $order->getItems();
        $this->assertCount(3, $orderItems);
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getProductType() == Type::TYPE_SIMPLE) {
                $this->assertNotEmpty($orderItem->getParentItem(), 'Parent is not set for child product');
                $this->assertNotEmpty($orderItem->getParentItemId(), 'Parent is not set for child product');
            }
        }
    }
}
