<?php
/**
 * Unit Test for \Magento\Framework\Validator\Config
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
namespace Magento\Framework\Validator;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Validator\Config
     */
    protected $_config;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage There must be at least one configuration file specified.
     */
    public function testConstructException()
    {
        $this->_initConfig(array());
    }

    /**
     * Inits $_serviceConfig property with specific files or default valid configuration files
     *
     * @param array|null $files
     */
    protected function _initConfig(array $files = null)
    {
        if (null === $files) {
            $files = glob(__DIR__ . '/_files/validation/positive/*/validation.xml');
        }
        $configFiles = array();
        foreach ($files as $path) {
            $configFiles[$path] = file_get_contents($path);
        }
        $config = new \Magento\Framework\ObjectManager\Config\Config(
            new \Magento\Framework\ObjectManager\Relations\Runtime()
        );
        $factory = new \Magento\Framework\ObjectManager\Factory\Factory($config);
        $realObjectManager = new \Magento\Framework\ObjectManager\ObjectManager($factory, $config);
        $factory->setObjectManager($realObjectManager);
        $universalFactory = $realObjectManager->get('\Magento\Framework\Validator\UniversalFactory');
        $this->_config = $this->_objectManager->getObject(
            'Magento\Framework\Validator\Config',
            array('configFiles' => $configFiles, 'builderFactory' => $universalFactory)
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown validation entity "invalid_entity"
     */
    public function testCreateValidatorInvalidEntityName()
    {
        $this->_initConfig();
        $this->_config->createValidatorBuilder('invalid_entity', null);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown validation group "invalid_group" in entity "test_entity_a"
     */
    public function testCreateValidatorInvalidGroupName()
    {
        $this->_initConfig();
        $this->_config->createValidatorBuilder('test_entity_a', 'invalid_group');
    }

    public function testCreateValidatorInvalidConstraintClass()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Constraint class "stdClass" must implement \Magento\Framework\Validator\ValidatorInterface'
        );
        $this->_initConfig(array(__DIR__ . '/_files/validation/negative/invalid_constraint.xml'));
        $this->_config->createValidator('test_entity', 'test_group');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Builder class "UnknownBuilderClass" was not found
     */
    public function testGetValidatorBuilderClassNotFound()
    {
        $this->_initConfig(array(__DIR__ . '/_files/validation/negative/invalid_builder_class.xml'));
        $this->_config->createValidatorBuilder('catalog_product', 'create');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Builder "stdClass" must extend \Magento\Framework\Validator\Builder
     */
    public function testGetValidatorBuilderInstanceInvalid()
    {
        $this->_initConfig(array(__DIR__ . '/_files/validation/negative/invalid_builder_instance.xml'));
        $this->_config->createValidatorBuilder('catalog_product', 'create');
    }

    /**
     * Test for getValidatorBuilder
     */
    public function testGetValidatorBuilderInstance()
    {
        $this->_initConfig();
        $builder = $this->_config->createValidatorBuilder('test_entity_a', 'check_alnum');
        $this->assertInstanceOf('Magento\Framework\Validator\Builder', $builder);
    }

    /**
     * @dataProvider getValidationRulesDataProvider
     *
     * @param string $entityName
     * @param string $groupName
     * @param mixed $value
     * @param bool $expectedResult
     * @param array $expectedMessages
     */
    public function testCreateValidator($entityName, $groupName, $value, $expectedResult, $expectedMessages)
    {
        $this->_initConfig();
        $validator = $this->_config->createValidator($entityName, $groupName);
        $actualResult = $validator->isValid($value);
        $this->assertEquals($expectedMessages, $validator->getMessages());
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Data provider for testCreateConfigForInvalidXml
     *
     * @return array
     */
    public function getValidationRulesDataProvider()
    {
        $result = array();

        // Case 1. Pass check alnum and int properties are not empty and have valid value
        $entityName = 'test_entity_a';
        $groupName = 'check_alnum_and_int_not_empty_and_have_valid_value';
        $value = new \Magento\Framework\Object(array('int' => 1, 'alnum' => 'abc123'));
        $expectedResult = true;
        $expectedMessages = array();
        $result[] = array($entityName, $groupName, $value, $expectedResult, $expectedMessages);

        // Case 2. Fail check alnum is not empty
        $value = new \Magento\Framework\Object(array('int' => 'abc123', 'alnum' => null));
        $expectedResult = false;
        $expectedMessages = array(
            'alnum' => array(
                'isEmpty' => 'Value is required and can\'t be empty',
                'alnumInvalid' => 'Invalid type given. String, integer or float expected'
            ),
            'int' => array('notInt' => '\'abc123\' does not appear to be an integer')
        );
        $result[] = array($entityName, $groupName, $value, $expectedResult, $expectedMessages);

        // Case 3. Pass check alnum has valid value
        $groupName = 'check_alnum';
        $value = new \Magento\Framework\Object(array('int' => 'abc123', 'alnum' => 'abc123'));
        $expectedResult = true;
        $expectedMessages = array();
        $result[] = array($entityName, $groupName, $value, $expectedResult, $expectedMessages);

        // Case 4. Fail check alnum has valid value
        $value = new \Magento\Framework\Object(array('int' => 'abc123', 'alnum' => '[abc123]'));
        $expectedResult = false;
        $expectedMessages = array(
            'alnum' => array('notAlnum' => '\'[abc123]\' contains characters which are non alphabetic and no digits')
        );
        $result[] = array($entityName, $groupName, $value, $expectedResult, $expectedMessages);

        return $result;
    }

    /**
     * Check builder configuration format
     */
    public function testBuilderConfiguration()
    {
        $this->getMockBuilder('Magento\Framework\Validator\Builder')->disableOriginalConstructor()->getMock();

        $this->_initConfig(array(__DIR__ . '/_files/validation/positive/builder/validation.xml'));
        $builder = $this->_config->createValidatorBuilder('test_entity_a', 'check_builder');
        $this->assertInstanceOf('Magento\Framework\Validator\Builder', $builder);

        $expected = array(
            array(
                'alias' => '',
                'class' => 'Magento\Framework\Validator\Test\NotEmpty',
                'options' => null,
                'property' => 'int',
                'type' => 'property'
            ),
            array(
                'alias' => 'stub',
                'class' => 'Validator_Stub',
                'options' => array(
                    'arguments' => array(
                        new \Magento\Framework\Validator\Constraint\Option('test_string_argument'),
                        new \Magento\Framework\Validator\Constraint\Option(
                            array('option1' => 'value1', 'option2' => 'value2')
                        ),
                        new \Magento\Framework\Validator\Constraint\Option\Callback(
                            array('Magento\Framework\Validator\Test\Callback', 'getId'),
                            null,
                            true
                        )
                    ),
                    'callback' => array(
                        new \Magento\Framework\Validator\Constraint\Option\Callback(
                            array('Magento\Framework\Validator\Test\Callback', 'configureValidator'),
                            null,
                            true
                        )
                    ),
                    'methods' => array(
                        'setOptionThree' => array(
                            'method' => 'setOptionThree',
                            'arguments' => array(
                                new \Magento\Framework\Validator\Constraint\Option(
                                    array('argOption' => 'argOptionValue')
                                ),
                                new \Magento\Framework\Validator\Constraint\Option\Callback(
                                    array('Magento\Framework\Validator\Test\Callback', 'getId'),
                                    null,
                                    true
                                ),
                                new \Magento\Framework\Validator\Constraint\Option('10')
                            )
                        ),
                        'enableOptionFour' => array('method' => 'enableOptionFour')
                    )
                ),
                'property' => 'int',
                'type' => 'property'
            )
        );
        $this->assertAttributeEquals($expected, '_constraints', $builder);
    }

    /**
     * Check XSD schema validates invalid config files
     *
     * @dataProvider getInvalidXmlFiles
     * @expectedException \Magento\Framework\Exception
     *
     * @param array|string $configFile
     */
    public function testValidateInvalidConfigFiles($configFile)
    {
        $this->_initConfig((array)$configFile);
    }

    /**
     * Data provider for testValidateInvalidConfigFiles
     *
     * @return array
     */
    public function getInvalidXmlFiles()
    {
        // TODO: add case There are no "entity_constraints" and "property_constraints" elements inside "rule" element
        return array(
            array(__DIR__ . '/_files/validation/negative/no_constraint.xml'),
            array(__DIR__ . '/_files/validation/negative/not_unique_use.xml'),
            array(__DIR__ . '/_files/validation/negative/no_rule_for_reference.xml'),
            array(__DIR__ . '/_files/validation/negative/no_name_for_entity.xml'),
            array(__DIR__ . '/_files/validation/negative/no_name_for_rule.xml'),
            array(__DIR__ . '/_files/validation/negative/no_name_for_group.xml'),
            array(__DIR__ . '/_files/validation/negative/no_class_for_constraint.xml'),
            array(__DIR__ . '/_files/validation/negative/invalid_method.xml'),
            array(__DIR__ . '/_files/validation/negative/invalid_method_callback.xml'),
            array(__DIR__ . '/_files/validation/negative/invalid_entity_callback.xml'),
            array(__DIR__ . '/_files/validation/negative/invalid_child_for_option.xml'),
            array(__DIR__ . '/_files/validation/negative/invalid_content_for_callback.xml'),
            array(__DIR__ . '/_files/validation/negative/multiple_callback_in_argument.xml')
        );
    }

    /**
     * Test schema file exists
     */
    public function testGetSchemaFile()
    {
        $this->_initConfig();
        $this->assertFileExists($this->_config->getSchemaFile());
    }
}
