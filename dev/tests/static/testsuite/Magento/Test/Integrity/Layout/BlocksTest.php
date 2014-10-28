<?php
/**
 * Test layout declaration and usage of block elements
 *
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
namespace Magento\Test\Integrity\Layout;

class BlocksTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected static $_containerAliases = array();

    /**
     * @var array
     */
    protected static $_blockAliases = array();

    /**
     * Collect declarations of containers per layout file that have aliases
     */
    public static function setUpBeforeClass()
    {
        foreach (\Magento\TestFramework\Utility\Files::init()->getLayoutFiles(array(), false) as $file) {
            $xml = simplexml_load_file($file);
            $elements = $xml->xpath('/layout//*[self::container or self::block]') ?: array();
            /** @var $node \SimpleXMLElement */
            foreach ($elements as $node) {
                $alias = (string)$node['as'];
                if (empty($alias)) {
                    $alias = (string)$node['name'];
                }
                if ($node->getName() == 'container') {
                    self::$_containerAliases[$alias]['files'][] = $file;
                    self::$_containerAliases[$alias]['names'][] = (string)$node['name'];
                } else {
                    self::$_blockAliases[$alias]['files'][] = $file;
                    self::$_blockAliases[$alias]['names'][] = (string)$node['name'];
                }
            }
        }
    }

    public function testBlocksNotContainers()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Check that containers are not used as blocks in templates
             *
             * @param string $alias
             * @param string $file
             * @throws \Exception|PHPUnit_Framework_ExpectationFailedException
             */
            function ($alias, $file) {
                if (isset(self::$_containerAliases[$alias])) {
                    if (!isset(self::$_blockAliases[$alias])) {
                        $this->fail(
                            "Element with alias '{$alias}' is used as a block in file '{$file}' ".
                            "via getChildBlock() method," .
                            " while '{$alias}' alias is declared as a container in file(s): " .
                            join(
                                ', ',
                                self::$_containerAliases[$alias]['files']
                            )
                        );
                    } else {
                        $this->markTestIncomplete(
                            "Element with alias '{$alias}' is used as a block in file '{$file}' ".
                            "via getChildBlock() method." .
                            " It's impossible to determine explicitly whether the element is a block or a container, " .
                            "as it is declared as a container in file(s): " .
                            join(
                                ', ',
                                self::$_containerAliases[$alias]['files']
                            ) . " and as a block in file(s): " . join(
                                ', ',
                                self::$_blockAliases[$alias]['files']
                            )
                        );
                    }
                }
            },
            $this->getChildBlockDataProvider()
        );
    }

    /**
     * @return array
     */
    public function getChildBlockDataProvider()
    {
        $result = array();
        foreach (\Magento\TestFramework\Utility\Files::init()->getPhpFiles(true, false, true, false) as $file) {
            $aliases = \Magento\TestFramework\Utility\Classes::getAllMatches(
                file_get_contents($file),
                '/\->getChildBlock\(\'([^\']+)\'\)/x'
            );
            foreach ($aliases as $alias) {
                $result[$file] = array($alias, $file);
            }
        }
        return $result;
    }
}
