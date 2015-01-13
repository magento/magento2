<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testPaymentMethods()
    {
        $invoker = new \Magento\Framework\Test\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Verify whether all payment methods are declared in appropriate modules
             */
            function ($configFile, $moduleName) {
                $config = simplexml_load_file($configFile);
                $nodes = $config->xpath('/config/default/payment/*/model') ?: [];
                $formalModuleName = str_replace('_', '\\', $moduleName);
                foreach ($nodes as $node) {
                    $this->assertStringStartsWith(
                        $formalModuleName . '\Model\\',
                        (string)$node,
                        "'{$node}' payment method is declared in '{$configFile}' module, " .
                        "but doesn't belong to '{$moduleName}' module"
                    );
                }
            },
            $this->paymentMethodsDataProvider()
        );
    }

    public function paymentMethodsDataProvider()
    {
        $data = [];
        foreach ($this->_getConfigFilesPerModule() as $configFile => $moduleName) {
            $data[] = [$configFile, $moduleName];
        }
        return $data;
    }

    /**
     * Get list of configuration files associated with modules
     *
     * @return array
     */
    protected function _getConfigFilesPerModule()
    {
        $configFiles = \Magento\Framework\Test\Utility\Files::init()->getConfigFiles('config.xml', [], false);
        $data = [];
        foreach ($configFiles as $configFile) {
            preg_match('#/([^/]+?/[^/]+?)/etc/config\.xml$#', $configFile, $moduleName);
            $moduleName = str_replace('/', '_', $moduleName[1]);
            $data[$configFile] = $moduleName;
        }
        return $data;
    }
}
