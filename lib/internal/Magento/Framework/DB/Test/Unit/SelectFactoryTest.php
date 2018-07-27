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
        $selectRenderer = $this->getMockBuilder('Magento\Framework\DB\Select\SelectRenderer')
            ->disableOriginalConstructor()
            ->getMock();
        $parts = [];
        $adapter = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->disableOriginalConstructor()
            ->getMock();
        $model = new \Magento\Framework\DB\SelectFactory($selectRenderer, $parts);
        $this->assertInstanceOf('Magento\Framework\DB\Select', $model->create($adapter));
    }
}
