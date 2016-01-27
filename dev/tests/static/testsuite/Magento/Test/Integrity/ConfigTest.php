<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity;

use Magento\Framework\App\Utility\Classes;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testPaymentMethods()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Verify whether all payment methods are declared in appropriate modules
             */
            function ($configFile, $moduleName) {
                $config = simplexml_load_file($configFile);
                $nodes = $config->xpath('/config/default/payment/*/model') ?: [];
                $formalModuleName = str_replace('_', '\\', $moduleName);
                foreach ($nodes as $node) {
                    if (!Classes::isVirtual((string)$node)) {
                        $this->assertStringStartsWith(
                            $formalModuleName . '\Model\\',
                            (string)$node,
                            "'{$node}' payment method is declared in '{$configFile}' module, " .
                            "but doesn't belong to '{$moduleName}' module"
                        );
                    }
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
        $data = [];
        $componentRegistrar = new \Magento\Framework\Component\ComponentRegistrar();
        $modulesPaths = $componentRegistrar->getPaths(\Magento\Framework\Component\ComponentRegistrar::MODULE);

        foreach ($modulesPaths as $moduleName => $path) {
            if (file_exists($configFile = $path . '/etc/config.xml')) {
                $data[$configFile] = $moduleName;
            }
        }
        return $data;
    }
}
