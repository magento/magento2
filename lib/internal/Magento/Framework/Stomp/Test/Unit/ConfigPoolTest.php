<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp\Test\Unit;

use Magento\Framework\Stomp\Config;
use Magento\Framework\Stomp\ConfigFactory;
use Magento\Framework\Stomp\ConfigPool;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigPoolTest extends TestCase
{
    /**
     * @var ConfigFactory|MockObject
     */
    private $factory;

    /**
     * @var ConfigPool
     */
    private $model;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(ConfigFactory::class);
        $this->model = new ConfigPool($this->factory);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testGetConnection(): void
    {
        $config = $this->createMock(Config::class);
        $this->factory->expects($this->once())
            ->method('create')
            ->with(['connectionName' => 'stomp'])
            ->willReturn($config);
        $this->assertEquals($config, $this->model->get('stomp'));
        //test that object is cached
        $this->assertEquals($config, $this->model->get('stomp'));
    }
}
