<?php
/**
 * Unit test for \Magento\Core\Model\Validator\Factory
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
namespace Magento\Core\Model\Validator;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Module\Dir\Reader
     */
    protected $_config;

    /**
     * @var \Magento\TranslateInterface
     */
    protected $_translateAdapter;

    /**
     * @var \Magento\Validator\Config
     */
    protected $_validatorConfig;

    /**
     * @var \Magento\Translate\AdapterInterface|null
     */
    protected $_defaultTranslator = null;

    /**
     * Save default translator
     */
    protected function setUp()
    {
        $this->_defaultTranslator = \Magento\Validator\AbstractValidator::getDefaultTranslator();
        $this->_objectManager = $this->getMock('Magento\ObjectManager');
        $this->_validatorConfig = $this->getMockBuilder(
            'Magento\Validator\Config'
        )->setMethods(
            array('createValidatorBuilder', 'createValidator')
        )->disableOriginalConstructor()->getMock();

        $this->_objectManager->expects(
            $this->at(0)
        )->method(
            'create'
        )->with(
            'Magento\Translate\Adapter'
        )->will(
            $this->returnValue(new \Magento\Translate\Adapter())
        );

        $this->_objectManager->expects(
            $this->at(1)
        )->method(
            'create'
        )->with(
            'Magento\Validator\Config',
            array('configFiles' => array('/tmp/moduleOne/etc/validation.xml'))
        )->will(
            $this->returnValue($this->_validatorConfig)
        );

        // Config mock
        $this->_config = $this->getMockBuilder(
            'Magento\Module\Dir\Reader'
        )->setMethods(
            array('getConfigurationFiles')
        )->disableOriginalConstructor()->getMock();
        $this->_config->expects(
            $this->once()
        )->method(
            'getConfigurationFiles'
        )->with(
            'validation.xml'
        )->will(
            $this->returnValue(array('/tmp/moduleOne/etc/validation.xml'))
        );

        // Translate adapter mock
        $this->_translateAdapter = $this->getMockBuilder(
            'Magento\TranslateInterface'
        )->disableOriginalConstructor()->getMock();
    }

    /**
     * Restore default translator
     */
    protected function tearDown()
    {
        \Magento\Validator\AbstractValidator::setDefaultTranslator($this->_defaultTranslator);
        unset($this->_defaultTranslator);
    }

    /**
     * Test getValidatorConfig created correct validator config. Check that validator translator was initialized.
     */
    public function testGetValidatorConfig()
    {
        $factory = new \Magento\Core\Model\Validator\Factory(
            $this->_objectManager,
            $this->_config,
            $this->_translateAdapter
        );
        $actualConfig = $factory->getValidatorConfig();
        $this->assertInstanceOf('Magento\Validator\Config', $actualConfig, 'Object of incorrect type was created');

        // Check that validator translator was correctly instantiated
        $validatorTranslator = \Magento\Validator\AbstractValidator::getDefaultTranslator();
        $this->assertInstanceOf(
            'Magento\Translate\Adapter',
            $validatorTranslator,
            'Default validator translate adapter was not set correctly'
        );
    }

    /**
     * Test createValidatorBuilder call
     */
    public function testCreateValidatorBuilder()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_validatorConfig->expects(
            $this->once()
        )->method(
            'createValidatorBuilder'
        )->with(
            'test',
            'class',
            array()
        )->will(
            $this->returnValue($objectManager->getObject('Magento\Validator\Builder', array('constraints' => array())))
        );
        $factory = new \Magento\Core\Model\Validator\Factory(
            $this->_objectManager,
            $this->_config,
            $this->_translateAdapter
        );
        $this->assertInstanceOf(
            'Magento\Validator\Builder',
            $factory->createValidatorBuilder('test', 'class', array())
        );
    }

    /**
     * Test createValidatorBuilder call
     */
    public function testCreateValidator()
    {
        $this->_validatorConfig->expects(
            $this->once()
        )->method(
            'createValidator'
        )->with(
            'test',
            'class',
            array()
        )->will(
            $this->returnValue(new \Magento\Validator())
        );
        $factory = new \Magento\Core\Model\Validator\Factory(
            $this->_objectManager,
            $this->_config,
            $this->_translateAdapter
        );
        $this->assertInstanceOf('Magento\Validator', $factory->createValidator('test', 'class', array()));
    }
}
