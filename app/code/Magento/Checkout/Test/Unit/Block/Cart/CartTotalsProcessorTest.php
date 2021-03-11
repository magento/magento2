<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Cart;

class CartTotalsProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Checkout\Block\Cart\CartTotalsProcessor
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfig;

    protected function setUp(): void
    {
        $this->scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->model = new \Magento\Checkout\Block\Cart\CartTotalsProcessor($this->scopeConfig);
    }

    public function testProcess()
    {
        $configData = [
            'total_1' => 'sort_1',
            'total_2' => 'sort_2',
            'total_3' => 'sort_3'
        ];

        $jsLayout = [
            'components' => [
                'block-totals' => [
                    'children' => [
                        'total_1' => ['value' => 'value_1', 'sortOrder' => 0],
                        'total_2' => ['value' => 'value_1', 'sortOrder' => 1],
                        'total_3' => ['value' => 'value_1', 'sortOrder' => 2]
                    ]
                ]
            ]
        ];

        $expected = [
            'components' => [
                'block-totals' => [
                    'children' => [
                        'total_1' => ['value' => 'value_1', 'sortOrder' => 'sort_1'],
                        'total_2' => ['value' => 'value_1', 'sortOrder' => 'sort_2'],
                        'total_3' => ['value' => 'value_1', 'sortOrder' => 'sort_3']
                    ]
                ]
            ]
        ];

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('sales/totals_sort')
            ->willReturn($configData);

        $this->assertEquals($expected, $this->model->process($jsLayout));
    }
}
