<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Page\Config\Reader;

use Magento\Framework\View\Layout\Element;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Config\Reader\Head;

class HeadTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Head
     */
    protected $model;

    protected function setUp(): void
    {
        $this->model = new Head();
    }

    public function testInterpret()
    {
        $readerContextMock = $this->getMockBuilder(\Magento\Framework\View\Layout\Reader\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $structureMock = $this->getMockBuilder(\Magento\Framework\View\Page\Config\Structure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $readerContextMock->expects($this->once())
            ->method('getPageConfigStructure')
            ->willReturn($structureMock);

        $xml = file_get_contents(__DIR__ . '/../_files/template_head.xml');
        $element = new Element($xml);

        $structureMock->expects($this->at(0))
            ->method('setTitle')
            ->with('Test title')
            ->willReturnSelf();

        $structureMock->expects($this->at(1))
            ->method('setMetaData')
            ->with('meta_name', 'meta_content')
            ->willReturnSelf();

        $structureMock->expects($this->at(2))
            ->method('setMetaData')
            ->with('og:video:secure_url', 'https://secure.example.com/movie.swf')
            ->willReturnSelf();

        $structureMock->expects($this->at(3))
            ->method('setMetaData')
            ->with('og:locale:alternate', 'uk_UA')
            ->willReturnSelf();

        $structureMock->expects($this->at(4))
            ->method('addAssets')
            ->with('path/file-3.css', ['src' => 'path/file-3.css', 'media' => 'all', 'content_type' => 'css'])
            ->willReturnSelf();

        $structureMock->expects($this->at(5))
            ->method('addAssets')
            ->with('path/file.js', ['src' => 'path/file.js', 'defer' => 'defer', 'content_type' => 'js'])
            ->willReturnSelf();

        $structureMock->expects($this->at(6))
            ->method('addAssets')
            ->with('http://url.com', ['src' => 'http://url.com', 'src_type' => 'url'])
            ->willReturnSelf();

        $structureMock->expects($this->at(7))
            ->method('removeAssets')
            ->with('path/remove/file.css')
            ->willReturnSelf();

        $structureMock->expects($this->at(8))
            ->method('setElementAttribute')
            ->with(Config::ELEMENT_TYPE_HEAD, 'head_attribute_name', 'head_attribute_value')
            ->willReturnSelf();

        $structureMock->expects($this->at(9))
            ->method('addAssets')
            ->with(
                'path/file-1.css',
                ['src' => 'path/file-1.css', 'media' => 'all', 'content_type' => 'css', 'order' => 10]
            )
            ->willReturnSelf();

        $structureMock->expects($this->at(10))
            ->method('addAssets')
            ->with(
                'path/file-2.css',
                ['src' => 'path/file-2.css', 'media' => 'all', 'content_type' => 'css', 'order' => 30]
            )
            ->willReturnSelf();

        $this->assertEquals($this->model, $this->model->interpret($readerContextMock, $element->children()[0]));
    }
}
