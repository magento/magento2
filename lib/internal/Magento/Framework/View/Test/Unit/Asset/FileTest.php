<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Asset;

use Magento\Framework\View\Asset\ContextInterface;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\Minification;
use Magento\Framework\View\Asset\Source;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    /**
     * @var Source|MockObject
     */
    private $source;

    /**
     * @var ContextInterface|MockObject
     */
    private $context;

    /**
     * @var Minification|MockObject
     */
    private $minificationMock;

    /**
     * @var File
     */
    private $object;

    protected function setUp(): void
    {
        $this->source = $this->createMock(Source::class);
        $this->context = $this->getMockForAbstractClass(ContextInterface::class);
        $this->minificationMock = $this->getMockBuilder(Minification::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->minificationMock
            ->expects($this->any())
            ->method('addMinifiedSign')
            ->willReturnArgument(0);

        $this->object = new File(
            $this->source,
            $this->context,
            'dir/file.css',
            'Magento_Module',
            'css',
            $this->minificationMock
        );
    }

    public function testGetUrl()
    {
        $this->context->expects($this->once())->method('getBaseUrl')->willReturn('http://example.com/');
        $this->context->expects($this->once())->method('getPath')->willReturn('static');
        $this->assertEquals('http://example.com/static/Magento_Module/dir/file.css', $this->object->getUrl());
    }

    public function testGetContentType()
    {
        $this->assertEquals('css', $this->object->getContentType());
        $object = new File($this->source, $this->context, '', '', 'type', $this->minificationMock);
        $this->assertEquals('type', $object->getContentType());
    }

    /**
     * @param string $contextPath
     * @param string $module
     * @param string $filePath
     * @param string $expected
     * @dataProvider getPathDataProvider
     */
    public function testGetPath($contextPath, $module, $filePath, $expected)
    {
        $this->context->expects($this->once())->method('getPath')->willReturn($contextPath);
        $object = new File($this->source, $this->context, $filePath, $module, '', $this->minificationMock);
        $this->assertEquals($expected, $object->getPath());
    }

    /**
     * @return array
     */
    public function getPathDataProvider()
    {
        return [
            ['', '', '', ''],
            ['', '', 'c/d', 'c/d'],
            ['', 'b', '', 'b'],
            ['', 'b', 'c/d', 'b/c/d'],
            ['a', '', '', 'a'],
            ['a', '', 'c/d', 'a/c/d'],
            ['a', 'b', '', 'a/b'],
            ['a', 'b', 'c/d', 'a/b/c/d'],
        ];
    }

    public function testGetSourceFile()
    {
        $this->source->expects($this->once())
            ->method('getFile')
            ->with($this->object)
            ->willReturn('result');
        $this->assertEquals('result', $this->object->getSourceFile());
        $this->assertEquals('result', $this->object->getSourceFile()); // second time to assert in-memory caching
    }

    public function testGetSourceFileMissing()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Unable to resolve the source file for \'context/Magento_Module/dir/file.css\'');
        $this->context->expects($this->once())->method('getPath')->willReturn('context');
        $this->source->expects($this->once())->method('getFile')->willReturn(false);
        $this->object->getSourceFile();
    }

    /**
     * @param string $content
     *
     * @dataProvider getContentDataProvider
     */
    public function testGetContent($content)
    {
        $this->source->expects($this->exactly(2))
            ->method('getContent')
            ->with($this->object)
            ->willReturn($content);
        $this->assertEquals($content, $this->object->getContent());
        $this->assertEquals($content, $this->object->getContent()); // no in-memory caching for content
    }

    /**
     * @return array
     */
    public function getContentDataProvider()
    {
        return [
            'normal content' => ['content'],
            'empty content'  => [''],
        ];
    }

    public function testGetContentNotFound()
    {
        $this->expectException('Magento\Framework\View\Asset\File\NotFoundException');
        $this->expectExceptionMessage('Unable to get content for \'Magento_Module/dir/file.css\'');
        $this->source->expects($this->once())
            ->method('getContent')
            ->with($this->object)
            ->willReturn(false);
        $this->object->getContent();
    }

    public function testSimpleGetters()
    {
        $this->assertEquals('dir/file.css', $this->object->getFilePath());
        $this->assertSame($this->context, $this->object->getContext());
        $this->assertEquals('Magento_Module', $this->object->getModule());
    }
}
