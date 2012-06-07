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
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Core_Model_Config_Module.
 */
class Mage_Core_Model_Config_ModuleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $inputConfigFile
     * @param string $expectedConfigFile
     * @param array $allowedModules
     * @dataProvider constructorDataProvider
     */
    public function testConstructor($inputConfigFile, $expectedConfigFile, $allowedModules = array())
    {
        $model = new Mage_Core_Model_Config_Module(new Mage_Core_Model_Config_Base($inputConfigFile), $allowedModules);
        $this->assertXmlStringEqualsXmlFile($expectedConfigFile, $model->getXmlString());
    }

    public function constructorDataProvider()
    {
        return array(
            'sorting dependencies' => array(
                __DIR__ . '/_files/module_input.xml',
                __DIR__ . '/_files/module_sorted.xml',
            ),
            'disallowed modules' => array(
                __DIR__ . '/_files/module_input.xml',
                __DIR__ . '/_files/module_filtered.xml',
                array('Fixture_ModuleOne', 'Fixture_ModuleTwo'),
            ),
        );
    }

    /**
     * @param string $inputConfigFile
     * @param string $expectedException
     * @param string $expectedExceptionMsg
     * @param array $allowedModules
     * @dataProvider constructorExceptionDataProvider
     */
    public function testConstructorException(
        $inputConfigFile, $expectedException, $expectedExceptionMsg, $allowedModules = array()
    ) {
        $this->setExpectedException($expectedException, $expectedExceptionMsg);
        new Mage_Core_Model_Config_Module(new Mage_Core_Model_Config_Base($inputConfigFile), $allowedModules);
    }

    public function constructorExceptionDataProvider()
    {
        return array(
            'linear dependency' => array(
                __DIR__ . '/_files/module_dependency_linear_input.xml',
                'Magento_Exception',
                "Module 'Fixture_Module' requires module 'Fixture_NonExistingModule'.",
            ),
            'circular dependency' => array(
                __DIR__ . '/_files/module_dependency_circular_input.xml',
                'Magento_Exception',
                "Module 'Fixture_ModuleTwo' cannot depend on 'Fixture_ModuleOne' since it creates circular dependency.",
            ),
            'soft circular dependency' => array(
                __DIR__ . '/_files/module_dependency_circular_soft_input.xml',
                'Magento_Exception',
                "Module 'Fixture_ModuleTwo' cannot depend on 'Fixture_ModuleOne' since it creates circular dependency.",
            ),
            'wrong dependency type' => array(
                __DIR__ . '/_files/module_dependency_wrong_input.xml',
                'UnexpectedValueException',
                'Unknown module dependency type \'wrong\' in declaration \'<Fixture_ModuleTwo type="wrong"/>\'.',
            ),
            'dependency on disallowed module' => array(
                __DIR__ . '/_files/module_input.xml',
                'Magento_Exception',
                "Module 'Fixture_ModuleTwo' requires module 'Fixture_ModuleOne'.",
                array('Fixture_ModuleTwo')
            )
        );
    }
}
