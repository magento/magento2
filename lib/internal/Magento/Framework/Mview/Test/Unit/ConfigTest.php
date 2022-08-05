<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\Test\Unit;

use Magento\Framework\Mview\Config;
use Magento\Framework\Mview\Config\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    protected $model;

    /**
     * @var MockObject|Data
     */
    protected $dataMock;

    protected function setUp(): void
    {
        $this->dataMock = $this->createMock(Data::class);
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
