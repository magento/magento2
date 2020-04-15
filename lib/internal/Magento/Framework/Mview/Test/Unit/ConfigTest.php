<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mview\Test\Unit;

use \Magento\Framework\Mview\Config;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Mview\Config
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Mview\Config\Data
     */
    protected $dataMock;

    protected function setUp(): void
    {
        $this->dataMock = $this->createMock(\Magento\Framework\Mview\Config\Data::class);
        $this->model = new Config(
            $this->dataMock
        );
    }

    public function testGetViews()
    {
        $this->dataMock->expects($this->once())
            ->method('get')
            ->willReturn(['some_data']);
        $this->assertEquals(['some_data'], $this->model->getViews());
    }

    public function testGetView()
    {
        $this->dataMock->expects($this->once())
            ->method('get')
            ->with('some_view')
            ->willReturn(['some_data']);
        $this->assertEquals(['some_data'], $this->model->getView('some_view'));
    }
}
