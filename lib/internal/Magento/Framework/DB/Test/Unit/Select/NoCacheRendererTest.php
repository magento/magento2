<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DB\Test\Unit\Select;

use Magento\Framework\DB\Select;

/**
 * Class DistinctRendererTest
 */
class NoCacheRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DB\Select\UseCacheRenderer
     */
    protected $model;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->selectMock = $this->getMock(\Magento\Framework\DB\Select::class, ['getPart'], [], '', false);
        $this->model = $objectManager->getObject(\Magento\Framework\DB\Select\NoCacheRenderer::class);
    }

    public function testRenderNoPart()
    {
        $sql = 'SELECT';
        $this->selectMock->expects($this->once())
            ->method('getPart')
            ->with(Select::NO_CACHE)
            ->willReturn(false);
        $this->assertEquals($sql, $this->model->render($this->selectMock, $sql));
    }

    public function testRender()
    {
        $sql = 'SELECT';
        $expectedResult = $sql . ' ' . Select::SQL_NO_CACHE  . ' ';
        $this->selectMock->expects($this->once())
            ->method('getPart')
            ->with(Select::NO_CACHE)
            ->willReturn(true);
        $this->assertEquals($expectedResult, $this->model->render($this->selectMock, $sql));
    }
}
