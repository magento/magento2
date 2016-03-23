<?php
/**
 * Find "backend/system.xml" files and validate them
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Magento\Backend;

class SystemConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testSchema()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $configFile
             */
            function ($configFile) {
                $dom = new \DOMDocument();
                $dom->loadXML(file_get_contents($configFile));
                $urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
                $schema =  $urnResolver->getRealPath('urn:magento:module:Magento_Config:etc/system_file.xsd');
                $errors = \Magento\Framework\Config\Dom::validateDomDocument($dom, $schema);
                if ($errors) {
                    $this->fail('XML-file has validation errors:' . PHP_EOL . implode(PHP_EOL . PHP_EOL, $errors));
                }
            },
            \Magento\Framework\App\Utility\Files::init()->getConfigFiles('adminhtml/system.xml', [])
        );
    }
}
