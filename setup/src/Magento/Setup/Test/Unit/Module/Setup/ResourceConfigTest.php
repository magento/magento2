<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Module\Setup;

class ResourceConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getConnectionNameDataProvider
     * @param string $resourceName
     */
    public function testGetConnectionName($resourceName)
    {
        $connectionName = \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION;
        $resourceConfig = new \Magento\Setup\Module\Setup\ResourceConfig();
        $this->assertEquals($connectionName, $resourceConfig->getConnectionName($resourceName));
    }

    /**
     * @return array
     */
    public function getConnectionNameDataProvider()
    {
        return [
            'validResourceName' => ['validResourceName'],
            'invalidResourceName' => ['invalidResourceName'],
            'blankResourceName' => ['']
        ];
    }
}
