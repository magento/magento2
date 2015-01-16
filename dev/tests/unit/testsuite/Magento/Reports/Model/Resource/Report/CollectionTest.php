<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Model\Resource\Report;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Reports\Model\Resource\Report\Collection
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryMock;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_factoryMock = $this->getMock(
            '\Magento\Reports\Model\DateFactory',
            ['create'],
            [],
            '',
            false
        );
        $arguments = ['dateFactory' => $this->_factoryMock];
        $this->_model = $helper->getObject('Magento\Reports\Model\Resource\Report\Collection', $arguments);
    }

    public function testGetIntervalsWithoutSpecifiedPeriod()
    {
        $startDate = date('m/d/Y', strtotime('-3 day'));
        $endDate = date('m/d/Y', strtotime('+3 day'));
        $this->_model->setInterval($startDate, $endDate);

        $startDateMock = $this->getMock('Magento\Framework\Stdlib\DateTime\DateInterface', [], [], '', false);
        $endDateMock = $this->getMock('Magento\Framework\Stdlib\DateTime\DateInterface', [], [], '', false);
        $map = [[$startDate, null, null, $startDateMock], [$endDate, null, null, $endDateMock]];
        $this->_factoryMock->expects($this->exactly(2))->method('create')->will($this->returnValueMap($map));
        $startDateMock->expects($this->once())->method('compare')->with($endDateMock)->will($this->returnValue(true));

        $this->assertEquals(0, $this->_model->getSize());
    }

    public function testGetIntervalsWithoutSpecifiedInterval()
    {
        $this->_factoryMock->expects($this->never())->method('create');
        $this->assertEquals(0, $this->_model->getSize());
    }
}
