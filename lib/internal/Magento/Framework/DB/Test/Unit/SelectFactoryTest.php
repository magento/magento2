<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit;

use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\SelectRenderer;
use Magento\Framework\DB\SelectFactory;
use PHPUnit\Framework\TestCase;

class SelectFactoryTest extends TestCase
{
    public function testCreate()
    {
        $selectRenderer = $this->getMockBuilder(SelectRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parts = [];
        $adapter = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();
        $model = new SelectFactory($selectRenderer, $parts);
        $this->assertInstanceOf(Select::class, $model->create($adapter));
    }
}
