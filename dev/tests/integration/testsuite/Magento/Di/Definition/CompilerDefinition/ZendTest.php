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
 * @category    Magento
 * @package     Magento_Di
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Di_Definition_CompilerDefinition_ZendTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Magento_Di_Definition_CompilerDefinition_Zend::addDirectory
     * @covers Magento_Di_Definition_CompilerDefinition_Zend::compile
     * @covers Magento_Di_Definition_CompilerDefinition_Zend::toArray
     */
    public function testCompiler()
    {
        $compiler = new Magento_Di_Definition_CompilerDefinition_Zend();

        $compiler->addDirectory(__DIR__ . '/TestAsset');
        $compiler->compile();

        $expectedDefinitions = include (__DIR__ . '/_files/definitions.php');
        $actualDefinitions   = $compiler->toArray();
        $this->assertEquals($expectedDefinitions, $actualDefinitions);
    }
}
