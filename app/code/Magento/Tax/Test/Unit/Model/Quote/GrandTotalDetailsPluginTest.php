<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Unit\Model\Quote;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class GrandTotalDetailsPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Api\Data\TotalSegmentExtensionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $totalSegmentExtensionFactoryMock;

    /**
     * @var \Magento\Tax\Api\Data\GrandTotalDetailsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $detailsFactoryMock;

    /**
     * @var \Magento\Tax\Api\Data\GrandTotalRatesInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ratesFactoryMock;

    /**
     * @var \Magento\Tax\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxConfigMock;

    /**
     * @var \Magento\Quote\Model\Cart\TotalsConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var \Closure|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $closureMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Tax\Model\Quote\GrandTotalDetailsPlugin
     */
    protected $model;

    protected function setUp()
    {
        $this->subjectMock = $this->getMockBuilder('\Magento\Quote\Model\Cart\TotalsConverter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->totalSegmentExtensionFactoryMock = $this->getMockBuilder(
            '\Magento\Quote\Api\Data\TotalSegmentExtensionFactory'
        )->disableOriginalConstructor()
            ->getMock();

        $this->detailsFactoryMock = $this->getMockBuilder('\Magento\Tax\Api\Data\GrandTotalDetailsInterfaceFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->ratesFactoryMock = $this->getMockBuilder('\Magento\Tax\Api\Data\GrandTotalRatesInterfaceFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->taxConfigMock = $this->getMockBuilder('\Magento\Tax\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManager($this);
        $this->model = $this->objectManagerHelper->getObject(
            '\Magento\Tax\Model\Quote\GrandTotalDetailsPlugin',
            [
                'totalSegmentExtensionFactory' => $this->totalSegmentExtensionFactoryMock,
                'ratesFactory' => $this->ratesFactoryMock,
                'detailsFactory' => $this->detailsFactoryMock,
                'taxConfig' => $this->taxConfigMock,
            ]
        );
    }

    protected function setupTaxTotal(array $data)
    {
        $taxTotalMock = $this->getMockBuilder('\Magento\Quote\Model\Quote\Address\Total')
            ->disableOriginalConstructor()
            ->getMock();

        $taxTotalMock->expects($this->any())
            ->method('getData')
            ->willReturn($data);

        return $taxTotalMock;
    }

    protected function setupTaxRateFactoryMock(array $taxRate)
    {
        $taxRateMock = $this->getMockBuilder('\Magento\Tax\Api\Data\GrandTotalRatesInterface')
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

    protected function setupTaxDetails(array $taxDetails)
    {
        $taxDetailsMock = $this->getMockBuilder('\Magento\Tax\Api\Data\GrandTotalDetailsInterface')
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

    public function testAroundProcess()
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
            'full_info' => [
                [
                    'amount' => $taxAmount,
                    'rates' => [$taxRate],
                ],
            ],
        ];
        $taxTotalMock = $this->setupTaxTotal($taxTotalData);
        $addressTotals = [
            'tax' => $taxTotalMock,
        ];

        $extensionAttributeMock = $this->getMockBuilder(
            '\Magento\Quote\Api\Data\TotalSegmentExtensionInterface'
        )->setMethods(
            [
                'setTaxGrandtotalDetails',

            ]
        )->getMockForAbstractClass();
        $extensionAttributeMock->expects($this->once())
            ->method('setTaxGrandtotalDetails')
            ->with([$taxDetailsMock])
            ->willReturnSelf();

        $taxSegmentMock = $this->getMockBuilder('\Magento\Quote\Model\Cart\TotalSegment')
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

        $this->closureMock = function () use ($totalSegments) {
            return $totalSegments;
        };

        $result = $this->model->aroundProcess($this->subjectMock, $this->closureMock, $addressTotals);
        $this->assertEquals($totalSegments, $result);
    }
}
