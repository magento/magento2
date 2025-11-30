<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Setup;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Module\Setup\ResourceConfig;
use PHPUnit\Framework\TestCase;

class ResourceConfigTest extends TestCase
{
    /**
     * @dataProvider getConnectionNameDataProvider
     * @param string $resourceName
     */
    public function testGetConnectionName($resourceName)
    {
        $connectionName = ResourceConnection::DEFAULT_CONNECTION;
        $resourceConfig = new ResourceConfig();
        $this->assertEquals($connectionName, $resourceConfig->getConnectionName($resourceName));
    }

    /**
     * @return array
     */
    public static function getConnectionNameDataProvider()
    {
        return [
            'validResourceName' => ['validResourceName'],
            'invalidResourceName' => ['invalidResourceName'],
            'blankResourceName' => ['']
        ];
    }
}
