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
 * Scans source code for references to classes and see if they indeed exist
 */
namespace Magento\Test\Legacy;

class ClassesTest extends \PHPUnit_Framework_TestCase
{
    public function testPhpCode()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $file
             */
            function ($file) {
                $classes = \Magento\TestFramework\Utility\Classes::collectPhpCodeClasses(file_get_contents($file));
                $this->_assertNonFactoryName($classes, $file);
            },
            \Magento\TestFramework\Utility\Files::init()->getPhpFiles()
        );
    }

    public function testConfiguration()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $path
             */
            function ($path) {
                $xml = simplexml_load_file($path);

                $classes = \Magento\TestFramework\Utility\Classes::collectClassesInConfig($xml);
                $this->_assertNonFactoryName($classes, $path);

                $modules = \Magento\TestFramework\Utility\Classes::getXmlAttributeValues($xml, '//@module', 'module');
                $this->_assertNonFactoryName(array_unique($modules), $path, false, true);
            },
            \Magento\TestFramework\Utility\Files::init()->getConfigFiles()
        );
    }

    public function testLayouts()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $path
             */
            function ($path) {
                $xml = simplexml_load_file($path);
                $classes = \Magento\TestFramework\Utility\Classes::collectLayoutClasses($xml);
                foreach (\Magento\TestFramework\Utility\Classes::getXmlAttributeValues(
                    $xml,
                    '/layout//@helper',
                    'helper'
                ) as $class) {
                    $classes[] = \Magento\TestFramework\Utility\Classes::getCallbackClass($class);
                }
                $classes = array_merge(
                    $classes,
                    \Magento\TestFramework\Utility\Classes::getXmlAttributeValues($xml, '/layout//@module', 'module')
                );
                $this->_assertNonFactoryName(array_unique($classes), $path);

                $tabs = \Magento\TestFramework\Utility\Classes::getXmlNodeValues(
                    $xml,
                    '/layout//action[@method="addTab"]/block'
                );
                $this->_assertNonFactoryName(array_unique($tabs), $path, true);
            },
            \Magento\TestFramework\Utility\Files::init()->getLayoutFiles()
        );
    }

    /**
     * Check whether specified classes or module names correspond to a file according PSR-1 Standard.
     *
     * Suppressing "unused variable" because of the "catch" block
     *
     * @param array $names
     * @param bool $softComparison
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _assertNonFactoryName($names, $file, $softComparison = false, $moduleBlock = false)
    {
        if (!$names) {
            return;
        }
        $factoryNames = array();
        foreach ($names as $name) {
            try {
                if ($softComparison) {
                    $this->assertNotRegExp('/\//', $name);
                } elseif ($moduleBlock) {
                    $this->assertFalse(false === strpos($name, '_'));
                    $this->assertRegExp('/^([A-Z][A-Za-z\d_]+)+$/', $name);
                } else {
                    if (strpos($name, 'Magento') === false) {
                        continue;
                    }
                    $this->assertFalse(false === strpos($name, '\\'));
                    $this->assertRegExp('/^([A-Z\\\\][A-Za-z\d\\\\]+)+$/', $name);
                }
            } catch (\PHPUnit_Framework_AssertionFailedError $e) {
                $factoryNames[] = $name;
            }
        }
        if ($factoryNames) {
            $this->fail("Obsolete factory name(s) detected in {$file}:" . "\n" . implode("\n", $factoryNames));
        }
    }
}
