<?php
/**
 * Unit test for Mage_Core_Model_Validator_Factory
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_Validator_FactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Mage_Core_Model_Config
     */
    protected $_config;

    /**
     * @var Mage_Core_Model_Translate
     */
    protected $_translateAdapter;

    /**
     * @var Magento_Validator_Config
     */
    protected $_validatorConfig;

    /**
     * @var Magento_Translate_AdapterInterface|null
     */
    protected $_defaultTranslator = null;

    /**
     * Save default translator
     */
    protected function setUp()
    {
        $this->_defaultTranslator = Magento_Validator_ValidatorAbstract::getDefaultTranslator();
        $this->_objectManager = $this->getMockBuilder('Magento_ObjectManager_Zend')
            ->setMethods(array('create', 'get'))
            ->disableOriginalConstructor()
            ->getMock();
        $this->_validatorConfig = $this->getMockBuilder('Magento_Validator_Config')
            ->setMethods(array('createValidatorBuilder', 'createValidator'))
            ->disableOriginalConstructor()
            ->getMock();
        $this->_objectManager->expects($this->once())
            ->method('get')
            ->with('Magento_Validator_Config', array('configFiles' => array('/tmp/moduleOne/etc/validation.xml')))
            ->will($this->returnValue($this->_validatorConfig));
        $this->_objectManager->expects($this->at(0))
            ->method('create')
            ->with('Magento_Translate_Adapter')
            ->will($this->returnValue(new Magento_Translate_Adapter()));

        // Config mock
        $this->_config = $this->getMockBuilder('Mage_Core_Model_Config')
            ->setMethods(array('getModuleConfigurationFiles'))
            ->disableOriginalConstructor()
            ->getMock();
        $this->_config->expects($this->once())
            ->method('getModuleConfigurationFiles')
            ->with('validation.xml')
            ->will($this->returnValue(array('/tmp/moduleOne/etc/validation.xml')));

        // Translate adapter mock
        $designMock = $this->getMock('Mage_Core_Model_Design_Package', array(), array(), '', false);
        $this->_translateAdapter = $this->getMockBuilder('Mage_Core_Model_Translate')
            ->setConstructorArgs(array($designMock))
            ->setMethods(array('_getTranslatedString'))
            ->getMock();
        $this->_translateAdapter->expects($this->any())
            ->method('_getTranslatedString')
            ->will($this->returnArgument(0));
    }

    /**
     * Restore default translator
     */
    protected function tearDown()
    {
        Magento_Validator_ValidatorAbstract::setDefaultTranslator($this->_defaultTranslator);
        unset($this->_defaultTranslator);
    }

    /**
     * Test getValidatorConfig created correct validator config. Check that validator translator was initialized.
     */
    public function testGetValidatorConfig()
    {
        $this->_objectManager->expects($this->at(2))
            ->method('create')
            ->with('Mage_Core_Model_Translate_Expr')
            ->will($this->returnValue(new Mage_Core_Model_Translate_Expr()));

        $factory = new Mage_Core_Model_Validator_Factory($this->_objectManager, $this->_config,
            $this->_translateAdapter);
        $actualConfig = $factory->getValidatorConfig();
        $this->assertInstanceOf('Magento_Validator_Config', $actualConfig,
            'Object of incorrect type was created');

        // Check that validator translator was correctly instantiated
        $validatorTranslator = Magento_Validator_ValidatorAbstract::getDefaultTranslator();
        $this->assertInstanceOf('Magento_Translate_Adapter', $validatorTranslator,
            'Default validator translate adapter was not set correctly');
        // Dive into callback
        /** @var Mage_Core_Model_Translate $translateAdapter */
        $this->assertEquals('Test message', $validatorTranslator->translate('Test message'),
            'Translator callback function was not initialized');
    }

    /**
     * Test createValidatorBuilder call
     */
    public function testCreateValidatorBuilder()
    {
        $this->_validatorConfig->expects($this->once())
            ->method('createValidatorBuilder')
            ->with('test', 'class', array())
            ->will($this->returnValue(new Magento_Validator_Builder(array())));
        $factory = new Mage_Core_Model_Validator_Factory($this->_objectManager, $this->_config,
            $this->_translateAdapter);
        $this->assertInstanceOf('Magento_Validator_Builder',
            $factory->createValidatorBuilder('test', 'class', array()));
    }

    /**
     * Test createValidatorBuilder call
     */
    public function testCreateValidator()
    {
        $this->_validatorConfig->expects($this->once())
            ->method('createValidator')
            ->with('test', 'class', array())
            ->will($this->returnValue(new Magento_Validator()));
        $factory = new Mage_Core_Model_Validator_Factory($this->_objectManager, $this->_config,
            $this->_translateAdapter);
        $this->assertInstanceOf('Magento_Validator',
            $factory->createValidator('test', 'class', array()));
    }
}
