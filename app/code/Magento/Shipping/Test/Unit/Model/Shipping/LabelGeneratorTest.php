<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Shipping\Test\Unit\Model\Shipping;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Magento\Shipping\Model\Shipping\Labels;
use Magento\Shipping\Model\Shipping\LabelsFactory;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class LabelGeneratorTest
 *
 * Test class for \Magento\Shipping\Model\Shipping\LabelGenerator
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LabelGeneratorTest extends TestCase
{
    const CARRIER_CODE = 'fedex';

    const CARRIER_TITLE = 'Fedex carrier';

    /**
     * @var CarrierFactory|MockObject
     */
    private $carrierFactory;

    /**
     * @var LabelsFactory|MockObject
     */
    private $labelsFactory;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var TrackFactory|MockObject
     */
    private $trackFactory;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var LabelGenerator
     */
    private $labelGenerator;

    protected function setUp(): void
    {
        $this->carrierFactory = $this->getMockBuilder(CarrierFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->labelsFactory = $this->getMockBuilder(LabelsFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->trackFactory = $this->getMockBuilder(TrackFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->labelGenerator = new LabelGenerator(
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
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $order->expects(static::once())
            ->method('getShippingMethod')
            ->with(true)
            ->willReturn($this->getShippingMethodMock());

        /**
         * @var $shipmentMock \Magento\Sales\Model\Order\Shipment|MockObject
         */
        $shipmentMock = $this->getMockBuilder(Shipment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $shipmentMock->expects(static::once())->method('getOrder')->willReturn($order);

        $this->carrierFactory->expects(static::once())
            ->method('create')
            ->with(self::CARRIER_CODE)
            ->willReturn($this->getCarrierMock());

        $labelsMock = $this->getMockBuilder(Labels::class)
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
            ->willReturn($this->getMockForAbstractClass(WriteInterface::class));

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

        $trackMock = $this->getMockBuilder(Track::class)
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
         * @var $requestMock \Magento\Framework\App\RequestInterface|MockObject
         */
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->labelGenerator->create($shipmentMock, $requestMock);
    }

    /**
     * @return MockObject
     */
    private function getShippingMethodMock()
    {
        $shippingMethod = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCarrierCode'])
            ->getMock();
        $shippingMethod->expects(static::once())
            ->method('getCarrierCode')
            ->willReturn(self::CARRIER_CODE);

        return $shippingMethod;
    }

    /**
     * @return MockObject
     */
    private function getCarrierMock()
    {
        $carrierMock = $this->getMockBuilder(AbstractCarrier::class)
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
     * @return MockObject
     */
    private function getResponseMock(array $info)
    {
        $responseMock = $this->getMockBuilder(DataObject::class)
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
