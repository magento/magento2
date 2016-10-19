<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\ResourceModel;

/**
 * Class QuoteTest to verify isOrderIncrementIdUsed method behaviour
 */
class QuoteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote
     */
    private $_resourceModel;

    protected function setUp()
    {
        $this->_resourceModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Quote\Model\ResourceModel\Quote::class
        );
    }

    /**
     * Test to verify if isOrderIncrementIdUsed method works with numeric increment ids
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testIsOrderIncrementIdUsedNumericIncrementId()
    {
        $this->assertTrue($this->_resourceModel->isOrderIncrementIdUsed('100000001'));
    }

    /**
     * Test to verify if isOrderIncrementIdUsed method works with alphanumeric increment ids
     *
     * @magentoDataFixture Magento/Sales/_files/order_alphanumeric_id.php
     */
    public function testIsOrderIncrementIdUsedAlphanumericIncrementId()
    {
        $this->assertTrue($this->_resourceModel->isOrderIncrementIdUsed('M00000001'));
    }
}
