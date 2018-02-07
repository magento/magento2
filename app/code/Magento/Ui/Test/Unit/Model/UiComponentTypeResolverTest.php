<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Model;

use Magento\Ui\Model\UiComponentTypeResolver;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class UiComponentTypeResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UiComponentTypeResolver
     */
    private $model;

    /**
     * @var array
     */
    private $contentTypeMap = [];

    protected function setUp()
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
     * @dataProvider resolveDataProvider
     */
    public function testResolve($acceptType, $contentType)
    {
        $uiComponentContextMock = $this->getMock(ContextInterface::class);
        $uiComponentContextMock->expects($this->atLeastOnce())->method('getAcceptType')->willReturn($acceptType);

        $this->assertEquals($contentType, $this->model->resolve($uiComponentContextMock));
    }

    /**
     * @return array
     */
    public function resolveDataProvider()
    {
        return [
            ['json', 'application/json'],
            ['xml', 'application/xml'],
            ['html', 'text/html'],
            ['undefined', UiComponentTypeResolver::DEFAULT_CONTENT_TYPE]
        ];
    }
}
