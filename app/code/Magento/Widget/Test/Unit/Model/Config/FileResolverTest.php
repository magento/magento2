<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Unit\Model\Config;

use Magento\Framework\Component\ComponentRegistrar;
use \Magento\Widget\Model\Config\FileResolver;

class FileResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FileResolver
     */
    private $object;

    /**
     * @var \Magento\Framework\Module\Dir\Reader|\PHPUnit\Framework\MockObject\MockObject
     */
    private $moduleReader;

    /**
     * @var \Magento\Framework\Config\FileIteratorFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $factory;

    /**
     * @var \Magento\Framework\Component\DirSearch|\PHPUnit\Framework\MockObject\MockObject
     */
    private $componentDirSearch;

    protected function setUp(): void
    {
        $this->moduleReader = $this->createMock(\Magento\Framework\Module\Dir\Reader::class);
        $this->factory = $this->createMock(\Magento\Framework\Config\FileIteratorFactory::class);
        $this->componentDirSearch = $this->createMock(\Magento\Framework\Component\DirSearch::class);
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
