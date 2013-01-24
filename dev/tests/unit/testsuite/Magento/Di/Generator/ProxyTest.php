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
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Di_Generator_ProxyTest extends Magento_Di_Generator_EntityTestAbstract
{
    /**#@+
     * Source and result class parameters
     */
    const SOURCE_CLASS = 'Magento\Di\Generator\TestAsset\SourceClass';
    const RESULT_CLASS = 'SourceClassProxy';
    const RESULT_FILE  = 'SourceClassProxy.php';
    /**#@-*/

    /**
     * Expected factory methods
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
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
                'shortDescription' => 'Proxy constructor',
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
            'name'       => 'publicChildMethod',
            'parameters' =>
            array(
                array(
                    'name'              => 'classGenerator',
                    'passedByReference' => false,
                    'type'              => '\\Zend\\Code\\Generator\\ClassGenerator',
                ),
                array(
                    'name'              => 'param1',
                    'passedByReference' => false,
                    'defaultValue'      => '',
                ),
                array(
                    'name'              => 'param2',
                    'passedByReference' => false,
                    'defaultValue'      => '\\\\',
                ),
                array(
                    'name'              => 'param3',
                    'passedByReference' => false,
                    'defaultValue'      => '\'',
                ),
                array(
                    'name'              => 'array',
                    'passedByReference' => false,
                    'type'              => 'array',
                    'defaultValue'      =>
                    array(),
                ),
            ),
            'body'       => 'return $this->_objectManager->get(self::CLASS_NAME)->publicChildMethod($classGenerator, $param1, $param2, $param3, $array, $param5);',
            'docblock'   =>
            array(
                'shortDescription' => '{@inheritdoc}',
            ),
        ),
        array(
            'name'       => 'publicMethodWithReference',
            'parameters' =>
            array(
                array(
                    'name'              => 'classGenerator',
                    'passedByReference' => true,
                    'type'              => '\\Zend\\Code\\Generator\\ClassGenerator',
                ),
                array(
                    'name'              => 'array',
                    'passedByReference' => true,
                    'type'              => 'array',
                ),
            ),
            'body'       => 'return $this->_objectManager->get(self::CLASS_NAME)->publicMethodWithReference($classGenerator, $array);',
            'docblock'   =>
            array(
                'shortDescription' => '{@inheritdoc}',
            ),
        ),
        array(
            'name'       => 'publicChildWithoutParameters',
            'parameters' =>
            array(),
            'body'       => 'return $this->_objectManager->get(self::CLASS_NAME)->publicChildWithoutParameters();',
            'docblock'   =>
            array(
                'shortDescription' => '{@inheritdoc}',
            ),
        ),
        array(
            'name'       => 'publicParentMethod',
            'parameters' =>
            array(
                array(
                    'name'              => 'docBlockGenerator',
                    'passedByReference' => false,
                    'type'              => '\\Zend\\Code\\Generator\\DocBlockGenerator',
                ),
                array(
                    'name'              => 'param1',
                    'passedByReference' => false,
                    'defaultValue'      => '',
                ),
                array(
                    'name'              => 'param2',
                    'passedByReference' => false,
                    'defaultValue'      => '\\\\',
                ),
                array(
                    'name'              => 'param3',
                    'passedByReference' => false,
                    'defaultValue'      => '\'',
                ),
                array(
                    'name'              => 'array',
                    'passedByReference' => false,
                    'type'              => 'array',
                    'defaultValue'      =>
                    array(),
                ),
            ),
            'body'       => 'return $this->_objectManager->get(self::CLASS_NAME)->publicParentMethod($docBlockGenerator, $param1, $param2, $param3, $array);',
            'docblock'   =>
            array(
                'shortDescription' => '{@inheritdoc}',
            ),
        ),
        array(
            'name'       => 'publicParentWithoutParameters',
            'parameters' =>
            array(),
            'body'       => 'return $this->_objectManager->get(self::CLASS_NAME)->publicParentWithoutParameters();',
            'docblock'   =>
            array(
                'shortDescription' => '{@inheritdoc}',
            ),
        ),
    );
    // @codingStandardsIgnoreEnd

    /**
     * Model under test
     *
     * @var Magento_Di_Generator_Factory
     */
    protected $_model;

    protected function setUp()
    {
        // add param with null default value
        $value = new \Zend\Code\Generator\ValueGenerator(null, \Zend\Code\Generator\ValueGenerator::TYPE_NULL);
        static::$_expectedMethods[1]['parameters'][5] = array(
            'name'              => 'param5',
            'passedByReference' => false,
            'defaultValue'      => $value,
        );
        $ioObjectMock = $this->_getIoObjectMock();

        $methods = array('setExtendedClass', 'setName', 'addProperties', 'addMethods', 'setClassDocBlock', 'generate');
        $codeGeneratorMock = $this->_getCodeGeneratorMock($methods);
        $codeGeneratorMock->expects($this->once())
            ->method('setExtendedClass')
            ->with('\\'. self::SOURCE_CLASS)
            ->will($this->returnSelf());

        $autoLoaderMock = $this->_getAutoloaderMock();

        /** @var $ioObjectMock Magento_Di_Generator_Io */
        /** @var $codeGeneratorMock Magento_Di_Generator_CodeGenerator_Zend */
        /** @var $autoLoaderMock Magento_Autoload_IncludePath */
        $this->_model = new Magento_Di_Generator_Proxy(self::SOURCE_CLASS, self::RESULT_CLASS, $ioObjectMock,
            $codeGeneratorMock, $autoLoaderMock
        );
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    /**
     * @covers Magento_Di_Generator_Proxy::_getClassMethods
     * @covers Magento_Di_Generator_Proxy::_generateCode
     * @covers Magento_Di_Generator_Proxy::_getMethodInfo
     * @covers Magento_Di_Generator_Proxy::_getMethodParameterInfo
     * @covers Magento_Di_Generator_Proxy::_escapeDefaultValue
     * @covers Magento_Di_Generator_Proxy::_getMethodBody
     */
    public function testGenerate()
    {
        $result = $this->_model->generate();
        $this->assertTrue($result);
        $this->assertEmpty($this->_model->getErrors());
    }
}
