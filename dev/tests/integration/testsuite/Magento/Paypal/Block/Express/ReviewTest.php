<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Paypal\Block\Express\Review
 */
namespace Magento\Paypal\Block\Express;

class ReviewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testRenderAddress()
    {
        $quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test01', 'reserved_order_id');

        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Paypal\Block\Express\Review::class
        );
        $addressData = include __DIR__ . '/../../../Sales/_files/address_data.php';
        $address = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Quote\Model\Quote\Address::class,
            ['data' => $addressData]
        );
        $address->setAddressType('billing');
        $address->setQuote($quote);
        $this->assertContains('Los Angeles', $block->renderAddress($address));
    }
}
