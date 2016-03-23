<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Convert;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class OrderTest
 */
class OrderTest extends \PHPUnit_Framework_TestCase
{
    /** @var Order */
    protected $_model;

    protected function setUp()
    {
        $this->_model = Bootstrap::getObjectManager()->create('Magento\Sales\Model\Convert\Order');
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testConvertToCreditmemo()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order');
        $order->loadByIncrementId('100000001');
        //MAGETWO-45612 fix
        $order->setBaseShippingAmount(5);
        $this->assertNull($this->_model->toCreditmemo($order)->getBaseShippingAmount());
    }
}
