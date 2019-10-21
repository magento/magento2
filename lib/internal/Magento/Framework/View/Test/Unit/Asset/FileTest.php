<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Asset;

use Magento\Framework\View\Asset\File;

class FileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Asset\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    private $source;

    /**
     * @var \Magento\Framework\View\Asset\ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\Framework\View\Asset\Minification|\PHPUnit_Framework_MockObject_MockObject
     */
    private $minificationMock;

    /**
     * @var File
     */
    private $object;

    protected function setUp()
    {
        $this->source = $this->createMock(\Magento\Framework\View\Asset\Source::class);
        $this->context = $this->getMockForAbstractClass(\Magento\Framework\View\Asset\ContextInterface::class);
        $this->minificationMock = $this->getMockBuilder(\Magento\Framework\View\Asset\Minification::class)
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
        $this->context->expects($this->once())->method('getBaseUrl')->will($this->returnValue('http://example.com/'));
        $this->context->expects($this->once())->method('getPath')->will($this->returnValue('static'));
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
        $this->context->expects($this->once())->method('getPath')->will($this->returnValue($contextPath));
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
            ->will($this->returnValue('result'));
        $this->assertEquals('result', $this->object->getSourceFile());
        $this->assertEquals('result', $this->object->getSourceFile()); // second time to assert in-memory caching
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Unable to resolve the source file for 'context/Magento_Module/dir/file.css'
     */
    public function testGetSourceFileMissing()
    {
        $this->context->expects($this->once())->method('getPath')->will($this->returnValue('context'));
        $this->source->expects($this->once())->method('getFile')->will($this->returnValue(false));
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
            ->will($this->returnValue($content));
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

    /**
     * @expectedException \Magento\Framework\View\Asset\File\NotFoundException
     * @expectedExceptionMessage Unable to get content for 'Magento_Module/dir/file.css'
     */
    public function testGetContentNotFound()
    {
        $this->source->expects($this->once())
            ->method('getContent')
            ->with($this->object)
            ->will($this->returnValue(false));
        $this->object->getContent();
    }

    public function testSimpleGetters()
    {
        $this->assertEquals('dir/file.css', $this->object->getFilePath());
        $this->assertSame($this->context, $this->object->getContext());
        $this->assertEquals('Magento_Module', $this->object->getModule());
    }
}
