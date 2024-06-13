<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Config;

use Magento\Framework\App\Config\InitialConfigSource;
use Magento\Framework\App\DeploymentConfig\Reader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InitialConfigSourceTest extends TestCase
{
    /**
     * @var Reader|MockObject
     */
    private $reader;

    /**
     * @var string
     */
    private $configType;

    /**
     * @var InitialConfigSource
     */
    private $source;

    protected function setUp(): void
    {
        $this->reader = $this->getMockBuilder(Reader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configType = 'configType';
        $this->source = new InitialConfigSource($this->reader, $this->configType);
    }

    public function testGet()
    {
        $path = 'path';
        $this->reader->expects($this->once())
            ->method('load')
            ->willReturn([$this->configType => [$path => 'value']]);
        $this->assertEquals('value', $this->source->get($path));
    }
}
