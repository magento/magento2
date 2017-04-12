<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit;

class SelectFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $selectRenderer = $this->getMockBuilder(\Magento\Framework\DB\Select\SelectRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parts = [];
        $adapter = $this->getMockBuilder(\Magento\Framework\DB\Adapter\Pdo\Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();
        $model = new \Magento\Framework\DB\SelectFactory($selectRenderer, $parts);
        $this->assertInstanceOf(\Magento\Framework\DB\Select::class, $model->create($adapter));
    }
}
