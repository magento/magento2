<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Unit\Model;

class WeeeConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $weeeHelperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $weeeConfigMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Weee\Model\WeeeConfigProvider
     */
    protected $model;

    protected function setUp(): void
    {
        $this->weeeHelperMock = $this->createMock(\Magento\Weee\Helper\Data::class);
        $this->weeeConfigMock = $this->createMock(\Magento\Weee\Model\Config::class);
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeMock = $this->createMock(\Magento\Store\Model\Store::class);

        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($this->storeMock);

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
        $this->storeMock->expects($this->any())->method('getId')->willReturn($storeId);
        $this->weeeHelperMock->expects($this->any())->method('isEnabled')->with($storeId)
            ->willReturn($weeeHelperEnabled);
        $this->weeeHelperMock->expects($this->any())->method('typeOfDisplay')
            ->willReturn($displayWeeeDetails);

        $this->weeeConfigMock->expects($this->any())->method('isEnabled')
            ->willReturn($weeeConfigEnabled);
        $this->weeeConfigMock->expects($this->any())->method('includeInSubtotal')
            ->willReturn($includeInSubtotal);

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
