<?php
/**
 * Unit test for \Magento\Core\Model\Validator\Factory
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\Validator;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $_config;

    /**
     * @var \Magento\Framework\TranslateInterface
     */
    protected $_translateAdapter;

    /**
     * @var \Magento\Framework\Validator\Config
     */
    protected $_validatorConfig;

    /**
     * @var \Magento\Framework\Translate\AdapterInterface|null
     */
    protected $_defaultTranslator = null;

    /**
     * Save default translator
     */
    protected function setUp()
    {
        $this->_defaultTranslator = \Magento\Framework\Validator\AbstractValidator::getDefaultTranslator();
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->_validatorConfig = $this->getMockBuilder(
            'Magento\Framework\Validator\Config'
        )->setMethods(
            ['createValidatorBuilder', 'createValidator']
        )->disableOriginalConstructor()->getMock();

        $this->_objectManager->expects(
            $this->at(0)
        )->method(
            'create'
        )->with(
            'Magento\Framework\Translate\Adapter'
        )->will(
            $this->returnValue(new \Magento\Framework\Translate\Adapter())
        );

        $this->_objectManager->expects(
            $this->at(1)
        )->method(
            'create'
        )->with(
            'Magento\Framework\Validator\Config',
            ['configFiles' => ['/tmp/moduleOne/etc/validation.xml']]
        )->will(
            $this->returnValue($this->_validatorConfig)
        );

        // Config mock
        $this->_config = $this->getMockBuilder(
            'Magento\Framework\Module\Dir\Reader'
        )->setMethods(
            ['getConfigurationFiles']
        )->disableOriginalConstructor()->getMock();
        $this->_config->expects(
            $this->once()
        )->method(
            'getConfigurationFiles'
        )->with(
            'validation.xml'
        )->will(
            $this->returnValue(['/tmp/moduleOne/etc/validation.xml'])
        );

        // Translate adapter mock
        $this->_translateAdapter = $this->getMockBuilder(
            'Magento\Framework\TranslateInterface'
        )->disableOriginalConstructor()->getMock();
    }

    /**
     * Restore default translator
     */
    protected function tearDown()
    {
        \Magento\Framework\Validator\AbstractValidator::setDefaultTranslator($this->_defaultTranslator);
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
        $this->assertInstanceOf(
            'Magento\Framework\Validator\Config',
            $actualConfig,
            'Object of incorrect type was created'
        );

        // Check that validator translator was correctly instantiated
        $validatorTranslator = \Magento\Framework\Validator\AbstractValidator::getDefaultTranslator();
        $this->assertInstanceOf(
            'Magento\Framework\Translate\Adapter',
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
            []
        )->will(
            $this->returnValue(
                $objectManager->getObject('Magento\Framework\Validator\Builder', ['constraints' => []])
            )
        );
        $factory = new \Magento\Core\Model\Validator\Factory(
            $this->_objectManager,
            $this->_config,
            $this->_translateAdapter
        );
        $this->assertInstanceOf(
            'Magento\Framework\Validator\Builder',
            $factory->createValidatorBuilder('test', 'class', [])
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
            []
        )->will(
            $this->returnValue(new \Magento\Framework\Validator())
        );
        $factory = new \Magento\Core\Model\Validator\Factory(
            $this->_objectManager,
            $this->_config,
            $this->_translateAdapter
        );
        $this->assertInstanceOf('Magento\Framework\Validator', $factory->createValidator('test', 'class', []));
    }
}
