<?php
/**
 * Test VS backwards-incompatible changes in widget.xml
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * A test for backwards-incompatible change in widget.xml structure
 */
namespace Magento\Test\Legacy\Magento\Widget;

class XmlTest extends \PHPUnit_Framework_TestCase
{
    public function testClassFactoryNames()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $file
             */
            function ($file) {
                $xml = simplexml_load_file($file);
                $nodes = $xml->xpath('/widgets/*[@type]') ?: [];
                /** @var \SimpleXMLElement $node */
                foreach ($nodes as $node) {
                    $type = (string)$node['type'];
                    $this->assertNotRegExp('/\//', $type, "Factory name detected: {$type}.");
                }
            },
            \Magento\Framework\App\Utility\Files::init()->getConfigFiles('widget.xml')
        );
    }

    public function testBlocksIntoContainers()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $file
             */
            function ($file) {
                $xml = simplexml_load_file($file);
                $this->assertSame(
                    [],
                    $xml->xpath('/widgets/*/supported_blocks'),
                    'Obsolete node: <supported_blocks>. To be replaced with <supported_containers>'
                );
                $this->assertSame(
                    [],
                    $xml->xpath('/widgets/*/*/*/block_name'),
                    'Obsolete node: <block_name>. To be replaced with <container_name>'
                );
            },
            \Magento\Framework\App\Utility\Files::init()->getConfigFiles('widget.xml')
        );
    }
}
