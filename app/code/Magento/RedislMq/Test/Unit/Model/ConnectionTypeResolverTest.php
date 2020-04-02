<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\RedisMq\Test\Unit\Model;

use Magento\RedisMq\Model\ConnectionTypeResolver;

/**
 * Unit tests for Redis connection type resolver
 */
class ConnectionTypeResolverTest extends \PHPUnit\Framework\TestCase
{
    public function testGetConnectionTypeWithDefaultValues()
    {
        $model = new ConnectionTypeResolver();
        $this->assertEquals('db', $model->getConnectionType('db'));
        $this->assertEquals(null, $model->getConnectionType('non-db'));
    }

    public function testGetConnectionTypeWithCustomValues()
    {
        $model = new ConnectionTypeResolver(['test-connection']);
        $this->assertEquals('db', $model->getConnectionType('db'));
        $this->assertEquals('db', $model->getConnectionType('test-connection'));
    }
}
