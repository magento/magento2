<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order;

class ShipmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\shipment
     */
    protected $shipmentModel;

    protected function setUp()
    {
        $helperManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $arguments = [];
        $this->shipmentModel = $helperManager->getObject('Magento\Sales\Model\Order\Shipment', $arguments);
    }

    public function testGetIncrementId()
    {
        $this->shipmentModel->setIncrementId('test_increment_id');
        $this->assertEquals('test_increment_id', $this->shipmentModel->getIncrementId());
    }
}
