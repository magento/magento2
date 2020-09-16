<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\App\Config\Source;

use Magento\Config\App\Config\Source\ModularConfigSource;
use Magento\Config\Model\Config\Structure\Reader as ConfigStructureReader;
use Magento\Framework\App\Config\Initial\Reader as InitialConfigReader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test config source that is retrieved from config.xml
 */
class ModularConfigSourceTest extends TestCase
{
    /**
     * @var InitialConfigReader|MockObject
     */
    private $initialConfigReader;

    /**
     * @var ConfigStructureReader|MockObject
     */
    private $configStructureReader;

    /**
     * @var ModularConfigSource
     */
    private $source;

    protected function setUp(): void
    {
        $this->initialConfigReader = $this->createMock(InitialConfigReader::class);
        $this->configStructureReader = $this->createMock(ConfigStructureReader::class);
        $this->source = new ModularConfigSource(
            $this->initialConfigReader,
            $this->configStructureReader
        );
    }

    public function testGet()
    {
        $this->initialConfigReader->expects($this->once())
            ->method('read')
            ->willReturn(['data' => ['path' => 'value']]);
        $this->assertEquals('value', $this->source->get('path'));
    }
}
