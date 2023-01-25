<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MysqlMq\Test\Unit\Model;

use Magento\MysqlMq\Model\ConnectionTypeResolver;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Mysql connection type resolver
 */
class ConnectionTypeResolverTest extends TestCase
{
    public function testGetConnectionTypeWithDefaultValues()
    {
        $model = new ConnectionTypeResolver();
        $this->assertEquals('db', $model->getConnectionType('db'));
        $this->assertNull($model->getConnectionType('non-db'));
    }

    public function testGetConnectionTypeWithCustomValues()
    {
        $model = new ConnectionTypeResolver(['test-connection']);
        $this->assertEquals('db', $model->getConnectionType('db'));
        $this->assertEquals('db', $model->getConnectionType('test-connection'));
    }
}
