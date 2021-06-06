<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model\Config\Processor;

use Magento\Store\Model\Config\Processor\Placeholder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PlaceholderTest extends TestCase
{
    /**
     * @var Placeholder
     */
    private $model;

    /**
     * @var \Magento\Store\Model\Config\Placeholder|MockObject
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

        $this->model = new Placeholder($this->configPlaceholderMock);
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
