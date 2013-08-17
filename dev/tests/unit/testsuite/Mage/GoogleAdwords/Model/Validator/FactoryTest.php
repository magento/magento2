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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Mage_GoogleAdwords_Model_Validator_ColorFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configurationMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_validatorBuilderFactoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_validatorBuilderMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_validatorMock;

    /**
     * @var Mage_GoogleAdwords_Model_Validator_Factory
     */
    protected $_factory;

    public function setUp()
    {
        $this->_helperMock = $this->getMock('Mage_GoogleAdwords_Helper_Data', array(), array(), '', false);
        $this->_helperMock->expects($this->any())->method('__')->with($this->isType('string'))
            ->will($this->returnCallback(
                function () {
                    $args = func_get_args();
                    $translated = array_shift($args);
                    return vsprintf($translated, $args);
                }
            ));

        $this->_validatorBuilderFactoryMock = $this->getMock('Magento_Validator_BuilderFactory', array('create'),
            array(), '', false);
        $this->_validatorBuilderMock = $this->getMock('Magento_Validator_Builder', array(), array(), '', false);
        $this->_validatorMock = $this->getMock('Magento_Validator_ValidatorInterface', array(), array(), '', false);

        $objectManager = new Magento_Test_Helper_ObjectManager($this);
        $this->_factory = $objectManager->getObject('Mage_GoogleAdwords_Model_Validator_Factory', array(
            'validatorBuilderFactory' => $this->_validatorBuilderFactoryMock,
            'helper' => $this->_helperMock,
        ));
    }

    public function testCreateColorValidator()
    {
        $currentColor = 'fff';
        $message = sprintf('Conversion Color value is not valid "%s". Please set hexadecimal 6-digit value.',
            $currentColor);

        $this->_validatorBuilderFactoryMock->expects($this->once())->method('create')
            ->with(array(
                'constraints' => array(
                    array(
                        'alias' => 'Regex',
                        'type' => '',
                        'class' => 'Magento_Validator_Regex',
                        'options' => array(
                            'arguments' => array('/^[0-9a-f]{6}$/i'),
                            'methods' => array(
                                array(
                                    'method' => 'setMessages',
                                    'arguments' => array(
                                        array(
                                            Magento_Validator_Regex::NOT_MATCH => $message,
                                            Magento_Validator_Regex::INVALID => $message,
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ))
            ->will($this->returnValue($this->_validatorBuilderMock));

        $this->_validatorBuilderMock->expects($this->once())->method('createValidator')
            ->will($this->returnValue($this->_validatorMock));

        $this->assertEquals($this->_validatorMock, $this->_factory->createColorValidator($currentColor));
    }

    public function testCreateConversionIdValidator()
    {
        $conversionId = '123';
        $message = sprintf('Conversion Id value is not valid "%s". Conversion Id should be an integer.', $conversionId);

        $this->_validatorBuilderFactoryMock->expects($this->once())->method('create')
            ->with(array(
                'constraints' => array(
                    array(
                        'alias' => 'Int',
                        'type' => '',
                        'class' => 'Magento_Validator_Int',
                        'options' => array(
                            'methods' => array(
                                array(
                                    'method' => 'setMessages',
                                    'arguments' => array(
                                        array(
                                            Magento_Validator_Int::NOT_INT => $message,
                                            Magento_Validator_Int::INVALID => $message,
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ))
            ->will($this->returnValue($this->_validatorBuilderMock));

        $this->_validatorBuilderMock->expects($this->once())->method('createValidator')
            ->will($this->returnValue($this->_validatorMock));

        $this->assertEquals($this->_validatorMock, $this->_factory->createConversionIdValidator($conversionId));
    }
}
