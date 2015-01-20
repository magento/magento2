<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Config\Source\Order;

use Magento\TestFramework\Helper\ObjectManager;

class StatusTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Sales\Model\Config\Source\Order\Status */
    protected $object;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var \Magento\Sales\Model\Order\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $config;

    protected function setUp()
    {
        $this->config = $this->getMock('Magento\Sales\Model\Order\Config', [], [], '', false);

        $this->objectManager = new ObjectManager($this);
        $this->object = $this->objectManager->getObject('Magento\Sales\Model\Config\Source\Order\Status', [
            'orderConfig' => $this->config
        ]);
    }

    public function testToOptionArray()
    {
        $this->config->expects($this->once())->method('getStateStatuses')
            ->will($this->returnValue(['status1', 'status2']));

        $this->assertEquals(
            [
                ['value' => '', 'label' => '-- Please Select --'],
                ['value' => 0, 'label' => 'status1'],
                ['value' => 1, 'label' => 'status2'],
            ],
            $this->object->toOptionArray()
        );
    }
}
