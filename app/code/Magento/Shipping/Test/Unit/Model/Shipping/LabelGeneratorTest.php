<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\Unit\Model\Shipping;

use Magento\Framework\DataObject;
use Magento\Sales\Model\Order\Shipment;
use Magento\Store\Model\ScopeInterface;

/**
 * Class LabelGeneratorTest
 *
 * Test class for \Magento\Shipping\Model\Shipping\LabelGenerator
 */
class LabelGeneratorTest extends \PHPUnit_Framework_TestCase
{
    const CARRIER_CODE = 'fedex';

    const CARRIER_TITLE = 'Fedex carrier';

    /**
     * @var \Magento\Shipping\Model\CarrierFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $carrierFactory;

    /**
     * @var \Magento\Shipping\Model\Shipping\LabelsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $labelsFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $trackFactory;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

    /**
     * @var \Magento\Shipping\Model\Shipping\LabelGenerator
     */
    private $labelGenerator;

    protected function setUp()
    {
        $this->carrierFactory = $this->getMockBuilder('Magento\Shipping\Model\CarrierFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->labelsFactory = $this->getMockBuilder('Magento\Shipping\Model\Shipping\LabelsFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->trackFactory = $this->getMockBuilder('Magento\Sales\Model\Order\Shipment\TrackFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->filesystem = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();

        $this->labelGenerator = new \Magento\Shipping\Model\Shipping\LabelGenerator(
            $this->carrierFactory,
            $this->labelsFactory,
            $this->scopeConfig,
            $this->trackFactory,
            $this->filesystem
        );
    }

    /**
     * @covers \Magento\Shipping\Model\Shipping\LabelGenerator
     * @param array $info
     * @dataProvider labelInfoDataProvider
     */
    public function testAddTrackingNumbersToShipment(array $info)
    {
        $order = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();
        $order->expects(static::once())
            ->method('getShippingMethod')
            ->with(true)
            ->willReturn($this->getShippingMethodMock());

        /**
         * @var $shipmentMock \Magento\Sales\Model\Order\Shipment|\PHPUnit_Framework_MockObject_MockObject
         */
        $shipmentMock = $this->getMockBuilder('Magento\Sales\Model\Order\Shipment')
            ->disableOriginalConstructor()
            ->getMock();
        $shipmentMock->expects(static::once())->method('getOrder')->willReturn($order);

        $this->carrierFactory->expects(static::once())
            ->method('create')
            ->with(self::CARRIER_CODE)
            ->willReturn($this->getCarrierMock());

        $labelsMock = $this->getMockBuilder('\Magento\Shipping\Model\Shipping\Labels')
            ->disableOriginalConstructor()
            ->getMock();
        $labelsMock->expects(static::once())
            ->method('requestToShipment')
            ->with($shipmentMock)
            ->willReturn($this->getResponseMock($info));

        $this->labelsFactory->expects(static::once())
            ->method('create')
            ->willReturn($labelsMock);

        $this->filesystem->expects(static::once())
            ->method('getDirectoryWrite')
            ->willReturn($this->getMock('Magento\Framework\Filesystem\Directory\WriteInterface'));

        $this->scopeConfig->expects(static::once())
            ->method('getValue')
            ->with(
                'carriers/' . self::CARRIER_CODE . '/title',
                ScopeInterface::SCOPE_STORE,
                null
            )->willReturn(self::CARRIER_TITLE);

        $this->labelsFactory->expects(static::once())
            ->method('create')
            ->willReturn($labelsMock);

        $trackMock = $this->getMockBuilder('Magento\Sales\Model\Order\Shipment\Track')
            ->setMethods(['setNumber', 'setCarrierCode', 'setTitle'])
            ->disableOriginalConstructor()
            ->getMock();

        $i = 0;
        $trackingNumbers = is_array($info['tracking_number']) ? $info['tracking_number'] : [$info['tracking_number']];
        foreach ($trackingNumbers as $trackingNumber) {
            $trackMock->expects(static::at($i++))
                ->method('setNumber')
                ->with($trackingNumber)
                ->willReturnSelf();
            $trackMock->expects(static::at($i++))
                ->method('setCarrierCode')
                ->with(self::CARRIER_CODE)
                ->willReturnSelf();
            $trackMock->expects(static::at($i++))
                ->method('setTitle')
                ->with(self::CARRIER_TITLE)
                ->willReturnSelf();
        }

        $this->trackFactory->expects(static::any())
            ->method('create')
            ->willReturn($trackMock);

        /**
         * @var $requestMock \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
         */
        $requestMock = $this->getMock('Magento\Framework\App\RequestInterface');
        $this->labelGenerator->create($shipmentMock, $requestMock);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getShippingMethodMock()
    {
        $shippingMethod = $this->getMockBuilder('Magento\Framework\DataObject')
            ->disableOriginalConstructor()
            ->setMethods(['getCarrierCode'])
            ->getMock();
        $shippingMethod->expects(static::once())
            ->method('getCarrierCode')
            ->willReturn(self::CARRIER_CODE);

        return $shippingMethod;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getCarrierMock()
    {
        $carrierMock = $this->getMockBuilder('Magento\Shipping\Model\Carrier\AbstractCarrier')
            ->disableOriginalConstructor()
            ->setMethods(['isShippingLabelsAvailable', 'getCarrierCode'])
            ->getMockForAbstractClass();
        $carrierMock->expects(static::once())
            ->method('isShippingLabelsAvailable')
            ->willReturn(true);
        $carrierMock->expects(static::once())
            ->method('getCarrierCode')
            ->willReturn(self::CARRIER_CODE);

        return $carrierMock;
    }

    /**
     * @param array $info
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getResponseMock(array $info)
    {
        $responseMock = $this->getMockBuilder('\Magento\Framework\DataObject')
            ->setMethods(['hasErrors', 'hasInfo', 'getInfo'])
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock->expects(static::once())
            ->method('hasErrors')
            ->willReturn(false);
        $responseMock->expects(static::once())
            ->method('hasInfo')
            ->willReturn(true);
        $responseMock->expects(static::once())
            ->method('getInfo')
            ->willReturn([$info]);

        return $responseMock;
    }

    /**
     * @return array
     */
    public function labelInfoDataProvider()
    {
        return [
            [['tracking_number' => ['111111', '222222', '333333'], 'label_content' => 'some']],
            [['tracking_number' => '111111', 'label_content' => 'some']],
        ];
    }
}
