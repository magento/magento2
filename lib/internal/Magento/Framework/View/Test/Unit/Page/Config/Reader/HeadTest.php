<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Page\Config\Reader;

use Magento\Framework\View\Layout\Element;
use Magento\Framework\View\Layout\Reader\Context;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Config\Reader\Head;
use Magento\Framework\View\Page\Config\Structure;
use Magento\Framework\Phrase;
use Magento\Framework\Phrase\RendererInterface;
use PHPUnit\Framework\Attributes\DataProvider;
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
        $expectedAssets = [
            'path/file-3.css' => ['src' => 'path/file-3.css', 'media' => 'all', 'content_type' => 'css'],
            'path/file.js' => ['src' => 'path/file.js', 'defer' => 'defer', 'content_type' => 'js'],
            'http://url.com' => ['src' => 'http://url.com', 'src_type' => 'url'],
            'path/file-1.css' => [
                'src' => 'path/file-1.css',
                'media' => 'all',
                'content_type' => 'css',
                'order' => 10
            ],
            'path/file-2.css' => [
                'src' => 'path/file-2.css',
                'media' => 'all',
                'content_type' => 'css',
                'order' => 30
            ],
        ];
        $structureMock
            ->method('addAssets')
            ->willReturnCallback(
                fn ($name, $attributes) => ($expectedAssets[$name] ?? null) == $attributes ? $structureMock : null
            );
        $expectedMetadata = [
            'meta_name' => 'meta_content',
            'og:video:secure_url' => 'https://secure.example.com/movie.swf',
            'og:locale:alternate' => 'uk_UA',
        ];
        $structureMock
            ->method('setMetaData')
            ->willReturnCallback(
                fn ($name, $content) => ($expectedMetadata[$name] ?? null) == $content ? $structureMock : null
            );

        $this->assertEquals($this->model, $this->model->interpret($readerContextMock, $element->children()[0]));
    }

    /**
     * @param string $translate
     * @param string $expected
     * @return void
     */
    #[DataProvider('metaTranslationProvider')]
    public function testInterpretTranslatesMetaContent(string $translate, string $expected): void
    {
        $readerContextMock = $this->createMock(Context::class);
        $structure = new Structure();
        $readerContextMock->method('getPageConfigStructure')->willReturn($structure);
        $xml = '<page><head><meta name="description" content="my english content"' . $translate . '/></head></page>';
        $element = new Element($xml);

        $renderer = $this->createMock(RendererInterface::class);
        $renderer->method('render')->willReturnCallback(
            fn (array $source) => 'TRANSLATED<' . implode('', $source) . '>'
        );
        $previousRenderer = Phrase::getRenderer();
        Phrase::setRenderer($renderer);
        try {
            $this->model->interpret($readerContextMock, $element->children()[0]);
        } finally {
            Phrase::setRenderer($previousRenderer);
        }

        $this->assertSame(['description' => $expected], $structure->getMetadata());
    }

    /**
     * @return array
     */
    public static function metaTranslationProvider(): array
    {
        return [
            'translate content' => [' translate="content"', 'TRANSLATED<my english content>'],
            'translate true' => [' translate="true"', 'TRANSLATED<my english content>'],
            'no translate attribute' => ['', 'my english content'],
            'translate false' => [' translate="false"', 'my english content'],
        ];
    }
}
