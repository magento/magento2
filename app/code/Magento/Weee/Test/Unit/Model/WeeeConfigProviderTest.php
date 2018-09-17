<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Unit\Model;

class WeeeConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $weeeHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $weeeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Weee\Model\WeeeConfigProvider
     */
    protected $model;

    protected function setUp()
    {
        $this->weeeHelperMock = $this->getMock('Magento\Weee\Helper\Data', [], [], '', false);
        $this->weeeConfigMock = $this->getMock('Magento\Weee\Model\Config', [], [], '', false);
        $this->storeManagerMock = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->storeMock = $this->getMock('Magento\Store\Model\Store', [], [], '', false);

        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($this->storeMock));

        $this->model = new \Magento\Weee\Model\WeeeConfigProvider(
            $this->weeeHelperMock,
            $this->storeManagerMock,
            $this->weeeConfigMock
        );
    }

    /**
     * @dataProvider getConfigDataProvider
     * @param array $expectedResult
     * @param bool $weeeHelperEnabled
     * @param bool $displayWeeeDetails
     * @param bool $weeeConfigEnabled
     * @param bool $includeInSubtotal
     */
    public function testGetConfig(
        $expectedResult,
        $weeeHelperEnabled,
        $displayWeeeDetails,
        $weeeConfigEnabled,
        $includeInSubtotal
    ) {
        $storeId = 1;
        $this->storeMock->expects($this->any())->method('getId')->will($this->returnValue($storeId));
        $this->weeeHelperMock->expects($this->any())->method('isEnabled')->with($storeId)
            ->will($this->returnValue($weeeHelperEnabled));
        $this->weeeHelperMock->expects($this->any())->method('typeOfDisplay')
            ->will($this->returnValue($displayWeeeDetails));

        $this->weeeConfigMock->expects($this->any())->method('isEnabled')
            ->will($this->returnValue($weeeConfigEnabled));
        $this->weeeConfigMock->expects($this->any())->method('includeInSubtotal')
            ->will($this->returnValue($includeInSubtotal));

        $this->assertEquals($expectedResult, $this->model->getConfig());
    }

    /**
     * @return array
     */
    public function getConfigDataProvider()
    {
        return [
            [
                'expectedResult' => [
                    'isDisplayPriceWithWeeeDetails' => false,
                    'isDisplayFinalPrice' => true,
                    'isWeeeEnabled' => false,
                    'isIncludedInSubtotal' => true,
                    'getIncludeWeeeFlag' => true,
                ],
                'weeeHelperEnabled' => false,
                'displayWeeeDetails' => true,
                'weeeConfigEnabled' => true,
                'includeInSubtotal' => true,
            ],
            [
                'expectedResult' => [
                    'isDisplayPriceWithWeeeDetails' => true,
                    'isDisplayFinalPrice' => true,
                    'isWeeeEnabled' => true,
                    'isIncludedInSubtotal' => true,
                    'getIncludeWeeeFlag' => true,
                ],
                'weeeHelperEnabled' => true,
                'displayWeeeDetails' => true,
                'weeeConfigEnabled' => true,
                'includeInSubtotal' => true,
            ],
            [
                'expectedResult' => [
                    'isDisplayPriceWithWeeeDetails' => false,
                    'isDisplayFinalPrice' => false,
                    'isWeeeEnabled' => true,
                    'isIncludedInSubtotal' => true,
                    'getIncludeWeeeFlag' => false,
                ],
                'weeeHelperEnabled' => true,
                'displayWeeeDetails' => false,
                'weeeConfigEnabled' => true,
                'includeInSubtotal' => true,
            ],
            [
                'expectedResult' => [
                    'isDisplayPriceWithWeeeDetails' => false,
                    'isDisplayFinalPrice' => false,
                    'isWeeeEnabled' => false,
                    'isIncludedInSubtotal' => true,
                    'getIncludeWeeeFlag' => false,
                ],
                'weeeHelperEnabled' => false,
                'displayWeeeDetails' => false,
                'weeeConfigEnabled' => true,
                'includeInSubtotal' => true,
            ],
            [
                'expectedResult' => [
                    'isDisplayPriceWithWeeeDetails' => false,
                    'isDisplayFinalPrice' => false,
                    'isWeeeEnabled' => false,
                    'isIncludedInSubtotal' => false,
                    'getIncludeWeeeFlag' => false,
                ],
                'weeeHelperEnabled' => false,
                'displayWeeeDetails' => false,
                'weeeConfigEnabled' => false,
                'includeInSubtotal' => true,
            ],
            [
                'expectedResult' => [
                    'isDisplayPriceWithWeeeDetails' => false,
                    'isDisplayFinalPrice' => false,
                    'isWeeeEnabled' => false,
                    'isIncludedInSubtotal' => false,
                    'getIncludeWeeeFlag' => false,
                ],
                'weeeHelperEnabled' => false,
                'displayWeeeDetails' => false,
                'weeeConfigEnabled' => true,
                'includeInSubtotal' => false,
            ],
            [
                'expectedResult' => [
                    'isDisplayPriceWithWeeeDetails' => false,
                    'isDisplayFinalPrice' => false,
                    'isWeeeEnabled' => false,
                    'isIncludedInSubtotal' => false,
                    'getIncludeWeeeFlag' => false,
                ],
                'weeeHelperEnabled' => false,
                'displayWeeeDetails' => false,
                'weeeConfigEnabled' => false,
                'includeInSubtotal' => false,
            ],
        ];
    }
}
