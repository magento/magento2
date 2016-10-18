<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Config;

use Magento\Framework\App\Config\ConfigSourceAggregated;
use Magento\Framework\App\Config\ConfigSourceInterface;

class ConfigSourceAggregatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigSourceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sourceMock;

    /**
     * @var ConfigSourceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sourceMockTwo;

    /**
     * @var ConfigSourceAggregated
     */
    private $source;

    public function setUp()
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
