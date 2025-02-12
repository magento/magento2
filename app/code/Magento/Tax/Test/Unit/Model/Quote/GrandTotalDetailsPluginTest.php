<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\Quote;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\TotalSegmentExtensionFactory;
use Magento\Quote\Api\Data\TotalSegmentExtensionInterface;
use Magento\Quote\Model\Cart\TotalsConverter;
use Magento\Quote\Model\Cart\TotalSegment;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Tax\Api\Data\GrandTotalDetailsInterface;
use Magento\Tax\Api\Data\GrandTotalDetailsInterfaceFactory;
use Magento\Tax\Api\Data\GrandTotalRatesInterface;
use Magento\Tax\Api\Data\GrandTotalRatesInterfaceFactory;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\Quote\GrandTotalDetailsPlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GrandTotalDetailsPluginTest extends TestCase
{
    /**
     * @var TotalSegmentExtensionFactory|MockObject
     */
    protected $totalSegmentExtensionFactoryMock;

    /**
     * @var GrandTotalDetailsInterfaceFactory|MockObject
     */
    protected $detailsFactoryMock;

    /**
     * @var GrandTotalRatesInterfaceFactory|MockObject
     */
    protected $ratesFactoryMock;

    /**
     * @var Config|MockObject
     */
    protected $taxConfigMock;

    /**
     * @var TotalsConverter|MockObject
     */
    protected $subjectMock;

    /**
     * @var ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var GrandTotalDetailsPlugin
     */
    protected $model;

    protected function setUp(): void
    {
        $this->subjectMock = $this->getMockBuilder(TotalsConverter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->totalSegmentExtensionFactoryMock = $this->getMockBuilder(
            TotalSegmentExtensionFactory::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->detailsFactoryMock = $this->getMockBuilder(
            GrandTotalDetailsInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->ratesFactoryMock = $this->getMockBuilder(GrandTotalRatesInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->taxConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $serializer = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $serializer->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );

        $serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        $this->objectManagerHelper = new ObjectManager($this);
        $this->model = $this->objectManagerHelper->getObject(
            GrandTotalDetailsPlugin::class,
            [
                'totalSegmentExtensionFactory' => $this->totalSegmentExtensionFactoryMock,
                'ratesFactory' => $this->ratesFactoryMock,
                'detailsFactory' => $this->detailsFactoryMock,
                'taxConfig' => $this->taxConfigMock,
                'serializer' => $serializer
            ]
        );
    }

    /**
     * @param array $data
     * @return MockObject
     */
    protected function setupTaxTotal(array $data)
    {
        $taxTotalMock = $this->getMockBuilder(Total::class)
            ->disableOriginalConstructor()
            ->getMock();

        $taxTotalMock->expects($this->any())
            ->method('getData')
            ->willReturn($data);

        return $taxTotalMock;
    }

    /**
     * @param array $taxRate
     * @return MockObject
     */
    protected function setupTaxRateFactoryMock(array $taxRate)
    {
        $taxRateMock = $this->getMockBuilder(GrandTotalRatesInterface::class)
            ->getMock();

        $this->ratesFactoryMock->expects($this->once())
            ->method('create')
            ->with([])
            ->willReturn($taxRateMock);

        $taxRateMock->expects($this->once())
            ->method('setPercent')
            ->with($taxRate['percent'])
            ->willReturnSelf();
        $taxRateMock->expects($this->once())
            ->method('setTitle')
            ->with($taxRate['title'])
            ->willReturnSelf();
        return $taxRateMock;
    }

    /**
     * @param array $taxDetails
     * @return MockObject
     */
    protected function setupTaxDetails(array $taxDetails)
    {
        $taxDetailsMock = $this->getMockBuilder(GrandTotalDetailsInterface::class)
            ->getMock();

        $this->detailsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($taxDetailsMock);

        $taxDetailsMock->expects($this->once())
            ->method('setAmount')
            ->with($taxDetails['amount'])
            ->willReturnSelf();

        $taxDetailsMock->expects($this->once())
            ->method('setRates')
            ->with($taxDetails['rates'])
            ->willReturnSelf();

        $taxDetailsMock->expects($this->once())
            ->method('setGroupId')
            ->with(1)
            ->willReturnSelf();

        return $taxDetailsMock;
    }

    public function testAfterProcess()
    {
        $taxRate = [
            'percent' => 8.25,
            'title' => 'TX',
        ];
        $taxAmount = 10;

        $taxRateMock = $this->setupTaxRateFactoryMock($taxRate);

        $taxDetailsMock = $this->setupTaxDetails(
            [
                'amount' => $taxAmount,
                'rates' => [$taxRateMock],
            ]
        );

        $taxTotalData = [
            'full_info' => json_encode([
                [
                    'amount' => $taxAmount,
                    'rates' => [$taxRate],
                ],
            ]),
        ];
        $taxTotalMock = $this->setupTaxTotal($taxTotalData);
        $addressTotals = [
            'tax' => $taxTotalMock,
        ];

        $extensionAttributeMock = $this->getMockBuilder(
            TotalSegmentExtensionInterface::class
        )->addMethods(
            [
                'setTaxGrandtotalDetails',

            ]
        )->getMockForAbstractClass();
        $extensionAttributeMock->expects($this->once())
            ->method('setTaxGrandtotalDetails')
            ->with([$taxDetailsMock])
            ->willReturnSelf();

        $taxSegmentMock = $this->getMockBuilder(TotalSegment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $taxSegmentMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributeMock);
        $taxSegmentMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($extensionAttributeMock)
            ->willReturnSelf();

        $totalSegments = [
            'tax' => $taxSegmentMock,
        ];

        $result = $this->model->afterProcess($this->subjectMock, $totalSegments, $addressTotals);
        $this->assertEquals($totalSegments, $result);
    }
}
