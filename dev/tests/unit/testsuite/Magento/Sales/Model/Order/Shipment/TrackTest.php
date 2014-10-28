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
namespace Magento\Sales\Model\Order\Shipment;

class TrackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Shipment\Track
     */
    protected $_model;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $arguments = array(
            'shipmentFactory' => $this->getMock(
                'Magento\Sales\Model\Order\ShipmentFactory',
                array(),
                array(),
                '',
                false
            )
        );

        $this->_model = $objectManagerHelper->getObject('Magento\Sales\Model\Order\Shipment\Track', $arguments);
    }

    public function testAddData()
    {
        $number = 123;
        $this->assertNull($this->_model->getTrackNumber());
        $this->_model->addData(array('number' => $number, 'test' => true));

        $this->assertTrue($this->_model->getTest());
        $this->assertEquals($number, $this->_model->getTrackNumber());
    }

    public function testGetStoreId()
    {
        $storeId = 10;
        $storeObject = new \Magento\Framework\Object(array('id' => $storeId));

        $shipmentMock = $this->getMock(
            'Magento\Sales\Model\Order\Shipment',
            array('getStore', '__wakeup'),
            array(),
            '',
            false
        );
        $shipmentMock->expects($this->once())->method('getStore')->will($this->returnValue($storeObject));

        $this->_model->setShipment($shipmentMock);
        $this->assertEquals($storeId, $this->_model->getStoreId());
    }

    public function testSetGetNumber()
    {
        $this->assertNull($this->_model->getNumber());
        $this->assertNull($this->_model->getTrackNumber());

        $this->_model->setNumber('test');

        $this->assertEquals('test', $this->_model->getNumber());
        $this->assertEquals('test', $this->_model->getTrackNumber());
    }

    /**
     * @dataProvider isCustomDataProvider
     * @param bool $expectedResult
     * @param string $carrierCodeToSet
     */
    public function testIsCustom($expectedResult, $carrierCodeToSet)
    {
        $this->_model->setCarrierCode($carrierCodeToSet);
        $this->assertEquals($expectedResult, $this->_model->isCustom());
    }

    /**
     * @return array
     */
    public static function isCustomDataProvider()
    {
        return array(array(true, \Magento\Sales\Model\Order\Shipment\Track::CUSTOM_CARRIER_CODE), array(false, 'ups'));
    }
}
