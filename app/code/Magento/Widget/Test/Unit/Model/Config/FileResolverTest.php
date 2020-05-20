<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Widget\Test\Unit\Model\Config;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\DirSearch;
use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\Module\Dir\Reader;
use Magento\Widget\Model\Config\FileResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileResolverTest extends TestCase
{
    /**
     * @var FileResolver
     */
    private $object;

    /**
     * @var Reader|MockObject
     */
    private $moduleReader;

    /**
     * @var FileIteratorFactory|MockObject
     */
    private $factory;

    /**
     * @var DirSearch|MockObject
     */
    private $componentDirSearch;

    protected function setUp(): void
    {
        $this->moduleReader = $this->createMock(Reader::class);
        $this->factory = $this->createMock(FileIteratorFactory::class);
        $this->componentDirSearch = $this->createMock(DirSearch::class);
        $this->object = new FileResolver($this->moduleReader, $this->factory, $this->componentDirSearch);
    }

    public function testGetGlobal()
    {
        $expected = new \stdClass();
        $this->moduleReader
            ->expects($this->once())
            ->method('getConfigurationFiles')
            ->with('file')
            ->willReturn($expected);
        $this->assertSame($expected, $this->object->get('file', 'global'));
    }

    public function testGetDesign()
    {
        $expected = new \stdClass();
        $this->componentDirSearch->expects($this->once())
            ->method('collectFiles')
            ->with(ComponentRegistrar::THEME, 'etc/file')
            ->willReturn(['test']);
        $this->factory->expects($this->once())->method('create')->with(['test'])->willReturn($expected);
        $this->assertSame($expected, $this->object->get('file', 'design'));
    }

    public function testGetDefault()
    {
        $expected = new \stdClass();
        $this->factory->expects($this->once())->method('create')->with([])->willReturn($expected);
        $this->assertSame($expected, $this->object->get('file', 'unknown'));
    }
}
