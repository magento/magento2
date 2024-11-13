<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Page\Config\Reader;

use Magento\Framework\View\Layout\Element;
use Magento\Framework\View\Layout\Reader\Context;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Config\Reader\Head;
use Magento\Framework\View\Page\Config\Structure;
use PHPUnit\Framework\TestCase;

class HeadTest extends TestCase
{
    /**
     * @var Head
     */
    protected $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->model = new Head();
    }

    /**
     * @return void
     */
    public function testInterpret(): void
    {
        $readerContextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $structureMock = $this->getMockBuilder(Structure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $readerContextMock->expects($this->once())
            ->method('getPageConfigStructure')
            ->willReturn($structureMock);

        $xml = file_get_contents(__DIR__ . '/../_files/template_head.xml');
        $element = new Element($xml);

        $structureMock
            ->method('setTitle')
            ->with('Test title')
            ->willReturn($structureMock);
        $structureMock
            ->method('setElementAttribute')
            ->with(Config::ELEMENT_TYPE_HEAD, 'head_attribute_name', 'head_attribute_value')
            ->willReturn($structureMock);
        $structureMock
            ->method('removeAssets')
            ->with('path/remove/file.css')
            ->willReturn($structureMock);
        $structureMock
            ->method('addAssets')
            ->willReturnCallback(function ($arg1, $arg2) use ($structureMock) {
                if ($arg1 == 'path/file-3.css' &&
                    $arg2 == ['src' => 'path/file-3.css', 'media' => 'all', 'content_type' => 'css']) {
                    return $structureMock;
                } elseif ($arg1 == 'path/file.js' &&
                    $arg2 == ['src' => 'path/file.js', 'defer' => 'defer', 'content_type' => 'js']) {
                    return $structureMock;
                } elseif ($arg1 == 'http://url.com' &&
                    $arg2 == ['src' => 'http://url.com', 'src_type' => 'url']) {
                    return $structureMock;
                } elseif ($arg1 == 'path/file-1.css' &&
                    $arg2 == ['src' => 'path/file-1.css', 'media' => 'all', 'content_type' => 'css', 'order' => 10]) {
                    return $structureMock;
                } elseif ($arg1 == 'path/file-2.css' &&
                    $arg2 == ['src' => 'path/file-2.css', 'media' => 'all', 'content_type' => 'css', 'order' => 30]) {
                    return $structureMock;
                }
            });
        $structureMock
            ->method('setMetaData')
            ->willReturnCallback(function ($arg1, $arg2) use ($structureMock) {
                if ($arg1 == 'meta_name' && $arg2 == 'meta_content') {
                    return $structureMock;
                } elseif ($arg1 == 'og:video:secure_url' && $arg2 == 'https://secure.example.com/movie.swf') {
                    return $structureMock;
                } elseif ($arg1 == 'og:locale:alternate' && $arg2 == 'uk_UA') {
                    return $structureMock;
                }
            });

        $this->assertEquals($this->model, $this->model->interpret($readerContextMock, $element->children()[0]));
    }
}
