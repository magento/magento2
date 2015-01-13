<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Setup\Module\Setup;

class ResourceConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getConnectionNameDataProvider
     * @param string $resourceName
     */
    public function testGetConnectionName($resourceName)
    {
        $connectionName = \Magento\Framework\App\Resource\Config::DEFAULT_SETUP_CONNECTION;
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
