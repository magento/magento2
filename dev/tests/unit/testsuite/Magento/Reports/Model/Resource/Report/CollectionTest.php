<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            array('create'),
            array(),
            '',
            false
        );
        $arguments = array('dateFactory' => $this->_factoryMock);
        $this->_model = $helper->getObject('Magento\Reports\Model\Resource\Report\Collection', $arguments);
    }

    public function testGetIntervalsWithoutSpecifiedPeriod()
    {
        $startDate = date('m/d/Y', strtotime('-3 day'));
        $endDate = date('m/d/Y', strtotime('+3 day'));
        $this->_model->setInterval($startDate, $endDate);

        $startDateMock = $this->getMock('Magento\Framework\Stdlib\DateTime\DateInterface', array(), array(), '', false);
        $endDateMock = $this->getMock('Magento\Framework\Stdlib\DateTime\DateInterface', array(), array(), '', false);
        $map = array(array($startDate, null, null, $startDateMock), array($endDate, null, null, $endDateMock));
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
