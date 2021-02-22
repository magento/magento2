<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model\Config\Processor;

/**
 * Class PlaceholderTest
 */
class PlaceholderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Model\Config\Processor\Placeholder
     */
    private $model;

    /**
     * @var \Magento\Store\Model\Config\Placeholder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configPlaceholderMock;

    protected function setUp(): void
    {
        $this->configPlaceholderMock = $this->createMock(\Magento\Store\Model\Config\Placeholder::class);

        $this->configPlaceholderMock->expects(
            $this->any()
        )->method(
            'process'
        )->withConsecutive(
            [['key1' => 'value1']],
            [['key2' => 'value2']]
        )->willReturnOnConsecutiveCalls(
            ['key1' => 'value1-processed'],
            ['key2' => 'value2-processed']
        );

        $this->model = new \Magento\Store\Model\Config\Processor\Placeholder($this->configPlaceholderMock);
    }

    public function testProcess()
    {
        $data = [
            'default' => ['key1' => 'value1'],
            'websites' => [
                'code' => ['key2' => 'value2']
            ]
        ];
        $expected = [
            'default' => ['key1' => 'value1-processed'],
            'websites' => [
                'code' => ['key2' => 'value2-processed']
            ]
        ];

        $this->assertEquals($expected, $this->model->process($data));
    }
}
