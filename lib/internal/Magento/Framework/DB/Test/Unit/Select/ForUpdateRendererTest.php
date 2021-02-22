<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DB\Test\Unit\Select;

use Magento\Framework\DB\Select;

/**
 * Class ForUpdateRendererTest
 */
class ForUpdateRendererTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\DB\Select\ForUpdateRenderer
     */
    protected $model;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $selectMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->selectMock = $this->createPartialMock(\Magento\Framework\DB\Select::class, ['getPart']);
        $this->model = $objectManager->getObject(\Magento\Framework\DB\Select\ForUpdateRenderer::class);
    }

    public function testRenderNoPart()
    {
        $sql = 'SELECT';
        $this->selectMock->expects($this->once())
            ->method('getPart')
            ->with(Select::FOR_UPDATE)
            ->willReturn(false);
        $this->assertEquals($sql, $this->model->render($this->selectMock, $sql));
    }

    public function testRender()
    {
        $sql = 'SELECT';
        $expectedResult = $sql . ' ' . Select::SQL_FOR_UPDATE;
        $this->selectMock->expects($this->once())
            ->method('getPart')
            ->with(Select::FOR_UPDATE)
            ->willReturn(true);
        $this->assertEquals($expectedResult, $this->model->render($this->selectMock, $sql));
    }
}
