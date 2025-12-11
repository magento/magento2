<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\App\Config\Source;

use Magento\Config\App\Config\Source\ModularConfigSource;
use Magento\Framework\App\Config\Initial\Reader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test config source that is retrieved from config.xml
 */
class ModularConfigSourceTest extends TestCase
{
    /**
     * @var Reader|MockObject
     */
    private $reader;

    /**
     * @var ModularConfigSource
     */
    private $source;

    protected function setUp(): void
    {
        $this->reader = $this->createMock(Reader::class);
        $this->source = new ModularConfigSource($this->reader);
    }

    public function testGet()
    {
        $this->reader->expects($this->once())
            ->method('read')
            ->willReturn(['data' => ['path' => 'value']]);
        $this->assertEquals('value', $this->source->get('path'));
    }
}
