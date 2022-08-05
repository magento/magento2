<?php
/**
 * Test layout declaration and usage of block elements
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Layout;

use Magento\Framework\App\Utility\Files;

class BlocksTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array
     */
    protected static $_containerAliases = [];

    /**
     * @var array
     */
    protected static $_blockAliases = [];

    /**
     * Collect declarations of containers per layout file that have aliases
     */
    public static function setUpBeforeClass(): void
    {
        foreach (Files::init()->getLayoutFiles([], false) as $file) {
            $xml = simplexml_load_file($file);
            $elements = $xml->xpath('/layout//*[self::container or self::block]') ?: [];
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
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Check that containers are not used as blocks in templates
             *
             * @param string $alias
             * @param string $file
             * @throws \Exception|PHPUnit\Framework\ExpectationFailedException
             */
            function ($alias, $file) {
                if (isset(self::$_containerAliases[$alias])) {
                    if (!isset(self::$_blockAliases[$alias])) {
                        $this->fail(
                            "Element with alias '{$alias}' is used as a block in file '{$file}' " .
                            "via getChildBlock() method," .
                            " while '{$alias}' alias is declared as a container in file(s): " .
                            join(
                                ', ',
                                self::$_containerAliases[$alias]['files']
                            )
                        );
                    } else {
                        $this->markTestIncomplete(
                            "Element with alias '{$alias}' is used as a block in file '{$file}' " .
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
        $result = [];
        $collectedFiles = Files::init()->getPhpFiles(
            Files::INCLUDE_APP_CODE
            | Files::INCLUDE_TEMPLATES
            | Files::INCLUDE_NON_CLASSES
        );
        foreach ($collectedFiles as $file) {
            $aliases = \Magento\Framework\App\Utility\Classes::getAllMatches(
                file_get_contents($file),
                '/\->getChildBlock\(\'([^\']+)\'\)/x'
            );
            foreach ($aliases as $alias) {
                $result[$file] = [$alias, $file];
            }
        }
        return $result;
    }
}
