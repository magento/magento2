<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Unit\Block\Checkout;

class TotalsProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Block\Checkout\TotalsProcessor
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMock(
            '\Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            [],
            '',
            false
        );

        $this->model = new \Magento\Checkout\Block\Checkout\TotalsProcessor($this->scopeConfigMock);
    }

    public function testProcess()
    {
        $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']
            ['children']['totals']['children'] = [
                'sub-total' => [],
                'grand-total' => [],
                'non-existant-total' => null
        ];
        $expectedResult['components']['checkout']['children']['sidebar']['children']['summary']
            ['children']['totals']['children'] = [
                'sub-total' => ['sortOrder' => 10],
                'grand-total' => ['sortOrder' => 20],
                'non-existant-total' => null
        ];
        $configData = ['sub_total' => 10, 'grand_total' => 20];

        $this->scopeConfigMock->expects($this->once())->method('getValue')->with('sales/totals_sort')
            ->willReturn($configData);

        $this->assertEquals($expectedResult, $this->model->process($jsLayout));
    }
}
