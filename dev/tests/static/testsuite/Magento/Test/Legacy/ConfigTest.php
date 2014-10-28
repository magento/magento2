<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tests for obsolete and removed config nodes
 */
namespace Magento\Test\Legacy;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigFiles()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $file
             */
            function ($file) {
                $obsoleteNodes = array();
                $obsoleteNodesFiles = glob(__DIR__ . '/_files/obsolete_config_nodes*.php');
                foreach ($obsoleteNodesFiles as $obsoleteNodesFile) {
                    $obsoleteNodes = array_merge($obsoleteNodes, include $obsoleteNodesFile);
                }

                $xml = simplexml_load_file($file);
                foreach ($obsoleteNodes as $xpath => $suggestion) {
                    $this->assertEmpty(
                        $xml->xpath($xpath),
                        "Nodes identified by XPath '{$xpath}' are obsolete. {$suggestion}"
                    );
                }
            },
            \Magento\TestFramework\Utility\Files::init()->getMainConfigFiles()
        );
    }
}
