<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Unit\Observer\Total\Webapi;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $weeeHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Weee\Observer\Total\Webapi\ItemObserver
     */
    protected $model;

    protected function setUp()
    {
        $this->weeeHelperMock = $this->getMock('Magento\Weee\Helper\Data', [], [], '', false);
        $this->storeManagerMock = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->storeMock = $this->getMock('Magento\Store\Model\Store', [], [], '', false);

        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($this->storeMock));

        $this->model = new \Magento\Weee\Observer\Total\Webapi\ItemObserver(
            $this->weeeHelperMock,
            $this->storeManagerMock
        );
    }

    /**
     * @dataProvider processTaxDataDataProvider
     *
     * @param bool $helperIsEnabled
     * @param int $weeeTaxInclTax
     * @param int $rowTotal
     * @param bool $weeeTaxRowApplied
     * @param int $rowTotalInclTax
     * @param int $rowWeeeInclTax
     * @param int $weeeTaxApplied
     * @param int $weeeTaxAppliedAmount
     * @param bool $includeWeeeFlag
     * @param int $priceIncTax
     * @param int $calculationPrice
     * @param int $expectedRowTotal
     * @param int $expectedRowInclTax
     * @param int $expectedPrice
     * @param int $expectedPriceInclTax
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function testProcessTaxData(
        $helperIsEnabled,
        $weeeTaxInclTax,
        $rowTotal,
        $weeeTaxRowApplied,
        $rowTotalInclTax,
        $rowWeeeInclTax,
        $weeeTaxApplied,
        $weeeTaxAppliedAmount,
        $includeWeeeFlag,
        $priceIncTax,
        $calculationPrice,
        $expectedRowTotal,
        $expectedRowInclTax,
        $expectedPrice,
        $expectedPriceInclTax
    ) {
        $observerMock = $this->getMock('Magento\Framework\Event\Observer', ['getEvent'], [], '', false);
        $eventMock = $this->getMock('Magento\Framework\Event', ['getItem'], [], '', false);
        $itemMock = $this->getMock(
            'Magento\Quote\Model\Quote\Item',
            [
                'setRowTotal', 'setRowTotalInclTax', 'setPrice', 'setPriceInclTax',
                'getPriceInclTax', 'getCalculationPrice', 'getRowTotal', 'getRowTotalInclTax', 'getWeeeTaxApplied',
                'getWeeeTaxAppliedRowAmount', 'getWeeeTaxAppliedAmount'
            ],
            [],
            '',
            false
        );

        $eventMock->expects($this->once())->method('getItem')->will($this->returnValue($itemMock));
        $observerMock->expects($this->once())->method('getEvent')->will($this->returnValue($eventMock));

        $this->weeeHelperMock->expects($this->any())->method('isEnabled')
            ->will($this->returnValue($helperIsEnabled));
        $this->weeeHelperMock->expects($this->any())->method('getWeeeTaxInclTax')
            ->with($itemMock)->will($this->returnValue($weeeTaxInclTax));
        $this->weeeHelperMock->expects($this->any())->method('getRowWeeeTaxInclTax')
            ->will($this->returnValue($rowWeeeInclTax));
        $this->weeeHelperMock->expects($this->any())->method('typeOfDisplay')
            ->will($this->returnValue($includeWeeeFlag));

        $weeeTaxApplied = serialize($weeeTaxApplied);
        $itemMock->expects($this->any())->method('getPriceInclTax')->will($this->returnValue($priceIncTax));
        $itemMock->expects($this->any())->method('getCalculationPrice')->will($this->returnValue($calculationPrice));
        $itemMock->expects($this->any())->method('getRowTotal')->will($this->returnValue($rowTotal));
        $itemMock->expects($this->any())->method('getRowTotalInclTax')->will($this->returnValue($rowTotalInclTax));
        $itemMock->expects($this->any())->method('getWeeeTaxApplied')->will($this->returnValue($weeeTaxApplied));
        $itemMock->expects($this->any())->method('getWeeeTaxAppliedAmount')
            ->will($this->returnValue($weeeTaxAppliedAmount));
        $itemMock->expects($this->any())->method('getWeeeTaxAppliedRowAmount')
            ->will($this->returnValue($weeeTaxRowApplied));

        $itemMock->expects($this->once())->method('setRowTotal')->with($expectedRowTotal)
            ->will($this->returnSelf());
        $itemMock->expects($this->once())->method('setRowTotalInclTax')->with($expectedRowInclTax)
            ->will($this->returnSelf());
        $itemMock->expects($this->once())->method('setPrice')->with($expectedPrice)
            ->will($this->returnSelf());
        $itemMock->expects($this->once())->method('setPriceInclTax')->with($expectedPriceInclTax)
            ->will($this->returnSelf());

        $this->model->execute($observerMock);
    }

    /**
     * @return array
     */
    public function processTaxDataDataProvider()
    {
        return [
            [
                'helperIsEnabled' => false,
                'weeeTaxInclTax' => 10,
                'rowTotal' => 17,
                'weeeTaxRowApplied' => 3,
                'rowTotalInclTax' => 11,
                'rowWeeeInclTax' => 14,
                'weeeTaxApplied' => 4,
                'weeeTaxAppliedAmount' => 4,
                'includeWeeeFlag' => false,
                'priceIncTax' => 5,
                'calculationPrice' => 12,
                'expectedRowTotal' => 17,
                'expectedRowInclTax' => 11,
                'expectedPrice' => 12,
                'expectedPriceInclTax' => 5
            ],
            [
                'helperIsEnabled' => true,
                'weeeTaxInclTax' => 10,
                'rowTotal' => 17,
                'weeeTaxRowApplied' => 3,
                'rowTotalInclTax' => 11,
                'rowWeeeInclTax' => 14,
                'weeeTaxApplied' => 4,
                'weeeTaxAppliedAmount' => 4,
                'includeWeeeFlag' => false,
                'priceIncTax' => 5,
                'calculationPrice' => 12,
                'expectedRowTotal' => 17,
                'expectedRowInclTax' => 11,
                'expectedPrice' => 12,
                'expectedPriceInclTax' => 5
            ],
            [
                'helperIsEnabled' => true,
                'weeeTaxInclTax' => 10,
                'rowTotal' => 17,
                'weeeTaxRowApplied' => 3,
                'rowTotalInclTax' => 11,
                'rowWeeeInclTax' => 14,
                'weeeTaxApplied' => 4,
                'weeeTaxAppliedAmount' => 4,
                'includeWeeeFlag' => true,
                'priceIncTax' => 5,
                'calculationPrice' => 12,
                'expectedRowTotal' => 20,
                'expectedRowInclTax' => 25,
                'expectedPrice' => 16,
                'expectedPriceInclTax' => 15
            ]
        ];
    }
}
