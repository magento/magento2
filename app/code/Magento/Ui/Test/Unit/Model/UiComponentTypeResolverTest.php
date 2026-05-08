<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Model;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Model\UiComponentTypeResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UiComponentTypeResolverTest extends TestCase
{
    /**
     * @var UiComponentTypeResolver
     */
    private $model;

    /**
     * @var array
     */
    private $contentTypeMap = [];

    protected function setUp(): void
    {
        $this->contentTypeMap = [
            'xml' => 'application/xml',
            'json' => 'application/json',
            'html' => 'text/html'
        ];
        $this->model = new UiComponentTypeResolver($this->contentTypeMap);
    }

    /**
     * @param string $acceptType
     * @param string $contentType     
     */
    #[DataProvider('resolveDataProvider')]
    public function testResolve(string $acceptType, string $contentType)
    {
        $uiComponentContextMock = $this->createMock(ContextInterface::class);
        $uiComponentContextMock->expects($this->atLeastOnce())->method('getAcceptType')->willReturn($acceptType);

        $this->assertEquals($contentType, $this->model->resolve($uiComponentContextMock));
    }

    /**
     * @return array
     */
    public static function resolveDataProvider(): array
    {
        return [
            ['json', 'application/json'],
            ['xml', 'application/xml'],
            ['html', 'text/html'],
            ['undefined', UiComponentTypeResolver::DEFAULT_CONTENT_TYPE]
        ];
    }
}
