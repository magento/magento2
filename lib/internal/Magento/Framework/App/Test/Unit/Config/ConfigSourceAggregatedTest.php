<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Config;

use Magento\Framework\App\Config\ConfigSourceAggregated;
use Magento\Framework\App\Config\ConfigSourceInterface;

class ConfigSourceAggregatedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConfigSourceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sourceMock;

    /**
     * @var ConfigSourceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sourceMockTwo;

    /**
     * @var ConfigSourceAggregated
     */
    private $source;

    protected function setUp(): void
    {
        $this->sourceMock = $this->getMockBuilder(ConfigSourceInterface::class)
            ->getMockForAbstractClass();
        $this->sourceMockTwo = $this->getMockBuilder(ConfigSourceInterface::class)
            ->getMockForAbstractClass();

        $sources = [
            [
                'source' => $this->sourceMockTwo,
                'sortOrder' => 100
            ],
            [
                'source' => $this->sourceMock,
                'sortOrder' => 10
            ],

        ];

        $this->source = new ConfigSourceAggregated($sources);
    }

    public function testGet()
    {
        $path = 'path';
        $this->sourceMock->expects($this->once())
            ->method('get')
            ->with($path)
            ->willReturn(['key' => 'value1', 'test' => false]);
        $this->sourceMockTwo->expects($this->once())
            ->method('get')
            ->with($path)
            ->willReturn(['key' => 'value2']);
        $this->assertEquals(
            [
                'test' => false,
                'key' => 'value2'
            ],
            $this->source->get($path)
        );
    }
}
