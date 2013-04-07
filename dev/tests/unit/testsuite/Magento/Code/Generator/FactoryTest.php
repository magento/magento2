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
 * @package     Magento_Code
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Code_Generator_FactoryTest extends Magento_Code_Generator_EntityTestAbstract
{
    /**#@+
     * Source and result class parameters
     */
    const SOURCE_CLASS = 'ClassName';
    const RESULT_CLASS = 'ClassNameFactory';
    const RESULT_FILE  = 'ClassNameFactory.php';
    /**#@-*/

    /**
     * Expected factory methods
     *
     * @var array
     */
    protected static $_expectedMethods = array(
        array(
            'name'       => '__construct',
            'parameters' =>
            array(
                array(
                    'name' => 'objectManager',
                    'type' => '\\Magento_ObjectManager',
                ),
            ),
            'body'       => '$this->_objectManager = $objectManager;',
            'docblock'   =>
            array(
                'shortDescription' => 'Factory constructor',
                'tags'             =>
                array(
                    array(
                        'name'        => 'param',
                        'description' => '\\Magento_ObjectManager $objectManager',
                    ),
                ),
            ),
        ),
        array(
            'name'       => 'create',
            'parameters' =>
            array(
                array(
                    'name'         => 'data',
                    'type'         => 'array',
                    'defaultValue' =>
                    array(),
                ),
            ),
            'body'       => 'return $this->_objectManager->create(self::CLASS_NAME, $data);',
            'docblock'   =>
            array(
                'shortDescription' => 'Create class instance with specified parameters',
                'tags'             =>
                array(
                    array(
                        'name'        => 'param',
                        'description' => 'array $data',
                    ),
                    array(
                        'name'        => 'return',
                        'description' => '\\ClassName',
                    ),
                ),
            ),
        ),
    );

    /**
     * Model under test
     *
     * @var Magento_Code_Generator_Factory
     */
    protected $_model;

    protected function setUp()
    {
        $ioObjectMock = $this->_getIoObjectMock();

        $methods = array('setImplementedInterfaces', 'setName', 'addProperties', 'addMethods', 'setClassDocBlock',
            'generate'
        );
        $codeGeneratorMock = $this->_getCodeGeneratorMock($methods);

        $autoLoaderMock = $this->_getAutoloaderMock();

        /** @var $ioObjectMock Magento_Code_Generator_Io */
        /** @var $codeGeneratorMock Magento_Code_Generator_CodeGenerator_Zend */
        /** @var $autoLoaderMock Magento_Autoload_IncludePath */
        $this->_model = new Magento_Code_Generator_Factory(self::SOURCE_CLASS, self::RESULT_CLASS, $ioObjectMock,
            $codeGeneratorMock, $autoLoaderMock
        );
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    /**
     * @covers Magento_Code_Generator_Factory::_generateCode
     * @covers Magento_Code_Generator_Factory::_getClassMethods
     */
    public function testGenerate()
    {
        $result = $this->_model->generate();
        $this->assertTrue($result);
        $this->assertEmpty($this->_model->getErrors());
    }
}
