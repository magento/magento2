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
use PHPUnit\Framework\Attributes\DataProvider;

class ResourceConfigTest extends TestCase
{
    /**
     * @param string $resourceName
     */
    #[DataProvider('getConnectionNameDataProvider')]
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
