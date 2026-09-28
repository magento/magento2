<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\ProductList;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\Product\ProductList\Toolbar;
use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

class ToolbarTest extends TestCase
{
    /**
     * @var Toolbar
     */
    protected $toolbarModel;

    /**
     * @var Http|MockObject
     */
    protected $requestMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(Http::class);
        $this->toolbarModel = (new ObjectManager($this))->getObject(
            Toolbar::class,
            [
                'request' => $this->requestMock,
            ]
        );
    }

    /**
     * @param $param
     */
    #[DataProvider('stringParamProvider')]
    public function testGetOrder($param)
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with(Toolbar::ORDER_PARAM_NAME)
            ->willReturn($param);
        $this->assertEquals($param, $this->toolbarModel->getOrder());
    }

    /**
     * @param $param
     */
    #[DataProvider('stringParamProvider')]
    public function testGetDirection($param)
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with(Toolbar::DIRECTION_PARAM_NAME)
            ->willReturn($param);
        $this->assertEquals($param, $this->toolbarModel->getDirection());
    }

    /**
     * @param $param
     */
    #[DataProvider('stringParamProvider')]
    public function testGetMode($param)
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with(Toolbar::MODE_PARAM_NAME)
            ->willReturn($param);
        $this->assertEquals($param, $this->toolbarModel->getMode());
    }

    /**
     * @param $param
     */
    #[DataProvider('stringParamProvider')]
    public function testGetLimit($param)
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with(Toolbar::LIMIT_PARAM_NAME)
            ->willReturn($param);
        $this->assertEquals($param, $this->toolbarModel->getLimit());
    }

    /**
     * @param $param
     */
    #[DataProvider('intParamProvider')]
    public function testGetCurrentPage($param)
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with(Toolbar::PAGE_PARM_NAME)
            ->willReturn($param);
        $this->assertEquals($param, $this->toolbarModel->getCurrentPage());
    }

    public function testGetCurrentPageNoParam()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with(Toolbar::PAGE_PARM_NAME)
            ->willReturn(false);
        $this->assertEquals(1, $this->toolbarModel->getCurrentPage());
    }

    /**
     * @return array
     */
    public static function stringParamProvider()
    {
        return [
            ['stringParam']
        ];
    }

    /**
     * @return array
     */
    public static function intParamProvider()
    {
        return [
            ['2'],
            [3]
        ];
    }
}
