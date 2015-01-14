<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource;

class QuoteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Resource\Quote
     */
    protected $_resourceModel;

    protected function setUp()
    {
        $this->_resourceModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Sales\Model\Resource\Quote'
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testIsOrderIncrementIdUsedNumericIncrementId()
    {
        $this->assertTrue($this->_resourceModel->isOrderIncrementIdUsed('100000001'));
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_alphanumeric_id.php
     */
    public function testIsOrderIncrementIdUsedAlphanumericIncrementId()
    {
        $this->assertTrue($this->_resourceModel->isOrderIncrementIdUsed('M00000001'));
    }
}
