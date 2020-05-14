<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model;

use Magento\Indexer\Model\Config;
use Magento\Indexer\Model\Config\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    protected $model;

    /**
     * @var Data|MockObject
     */
    protected $configMock;

    /**
     * Set up test
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createMock(Data::class);

        $this->model = new Config(
            $this->configMock
        );
    }

    public function testGetIndexers()
    {
        $this->configMock->expects($this->once())->method('get')->with()->willReturnSelf();
        $this->model->getIndexers();
    }

    public function testGetIndexer()
    {
        $indexerId = 1;
        $this->configMock->expects($this->once())->method('get')->with($indexerId)->willReturnSelf();
        $this->model->getIndexer($indexerId);
    }

    public function testGetNotExistingIndexer()
    {
        $indexerId = 1;
        $this->configMock
            ->expects($this->once())
            ->method('get')
            ->with($indexerId);
        $this->assertEquals([], $this->model->getIndexer($indexerId));
    }
}
