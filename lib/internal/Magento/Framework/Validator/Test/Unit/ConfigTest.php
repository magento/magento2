<?php
/**
 * Unit Test for \Magento\Framework\Validator\Config
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator\Test\Unit;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Validator\Config
     */
    protected $_config;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /** @var \Magento\Framework\Config\Dom\UrnResolver */
    protected $urnResolver;

    protected function setUp()
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage There must be at least one configuration file specified.
     */
    public function testConstructException()
    {
        $this->_initConfig([]);
    }

    /**
     * Inits $_serviceConfig property with specific files or default valid configuration files
     *
     * @param array|null $files
     */
    protected function _initConfig(array $files = null)
    {
        if (null === $files) {
            $files = glob(__DIR__ . '/_files/validation/positive/*/validation.xml', GLOB_NOSORT);
        }
        $configFiles = [];
        foreach ($files as $path) {
            $configFiles[$path] = file_get_contents($path);
        }
        $config = new \Magento\Framework\ObjectManager\Config\Config(
            new \Magento\Framework\ObjectManager\Relations\Runtime()
        );
        $factory = new \Magento\Framework\ObjectManager\Factory\Dynamic\Developer($config);
        $appObjectManager = new \Magento\Framework\ObjectManager\ObjectManager($factory, $config);
        $factory->setObjectManager($appObjectManager);
        /** @var \Magento\Framework\Validator\UniversalFactory $universalFactory */
        $universalFactory = $appObjectManager->get(\Magento\Framework\Validator\UniversalFactory::class);
        /** @var \Magento\Framework\Config\Dom\UrnResolver $urnResolverMock */
        $urnResolverMock = $this->createMock(\Magento\Framework\Config\Dom\UrnResolver::class);
        $urnResolverMock->expects($this->any())
            ->method('getRealPath')
            ->with('urn:magento:framework:Validator/etc/validation.xsd')
            ->willReturn($this->urnResolver->getRealPath('urn:magento:framework:Validator/etc/validation.xsd'));
        $appObjectManager->configure(
            [
                'preferences' => [
                    \Magento\Framework\Config\ValidationStateInterface::class =>
                        \Magento\Framework\App\Arguments\ValidationState::class,
                ],
                \Magento\Framework\App\Arguments\ValidationState::class => [
                    'arguments' => [
                        'appMode' => 'developer',
                    ]
                ]
            ]
        );
        $this->_config = $this->_objectManager->getObject(
            \Magento\Framework\Validator\Config::class,
            [
                'configFiles' => $configFiles,
                'builderFactory' => $universalFactory,
                'domFactory' => new \Magento\Framework\Config\DomFactory($appObjectManager),
                'urnResolver' => $urnResolverMock
            ]
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
        $this->expectException(
            'InvalidArgumentException',
            'Constraint class "stdClass" must implement \Magento\Framework\Validator\ValidatorInterface'
        );
        $this->_initConfig([__DIR__ . '/_files/validation/negative/invalid_constraint.xml']);
        $this->_config->createValidator('test_entity', 'test_group');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Builder class "UnknownBuilderClass" was not found
     */
    public function testGetValidatorBuilderClassNotFound()
    {
        $this->_initConfig([__DIR__ . '/_files/validation/negative/invalid_builder_class.xml']);
        $this->_config->createValidatorBuilder('catalog_product', 'create');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Builder "stdClass" must extend \Magento\Framework\Validator\Builder
     */
    public function testGetValidatorBuilderInstanceInvalid()
    {
        $this->_initConfig([__DIR__ . '/_files/validation/negative/invalid_builder_instance.xml']);
        $this->_config->createValidatorBuilder('catalog_product', 'create');
    }

    /**
     * Test for getValidatorBuilder
     */
    public function testGetValidatorBuilderInstance()
    {
        $this->_initConfig();
        $builder = $this->_config->createValidatorBuilder('test_entity_a', 'check_alnum');
        $this->assertInstanceOf(\Magento\Framework\Validator\Builder::class, $builder);
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
        $result = [];

        // Case 1. Pass check alnum and int properties are not empty and have valid value
        $entityName = 'test_entity_a';
        $groupName = 'check_alnum_and_int_not_empty_and_have_valid_value';
        $value = new \Magento\Framework\DataObject(['int' => 1, 'alnum' => 'abc123']);
        $expectedResult = true;
        $expectedMessages = [];
        $result[] = [$entityName, $groupName, $value, $expectedResult, $expectedMessages];

        // Case 2. Fail check alnum is not empty
        $value = new \Magento\Framework\DataObject(['int' => 'abc123', 'alnum' => null]);
        $expectedResult = false;
        $expectedMessages = [
            'alnum' => [
                'isEmpty' => 'Value is required and can\'t be empty',
                'alnumInvalid' => 'Invalid type given. String, integer or float expected',
            ],
            'int' => ['notInt' => '\'abc123\' does not appear to be an integer'],
        ];
        $result[] = [$entityName, $groupName, $value, $expectedResult, $expectedMessages];

        // Case 3. Pass check alnum has valid value
        $groupName = 'check_alnum';
        $value = new \Magento\Framework\DataObject(['int' => 'abc123', 'alnum' => 'abc123']);
        $expectedResult = true;
        $expectedMessages = [];
        $result[] = [$entityName, $groupName, $value, $expectedResult, $expectedMessages];

        // Case 4. Fail check alnum has valid value
        $value = new \Magento\Framework\DataObject(['int' => 'abc123', 'alnum' => '[abc123]']);
        $expectedResult = false;
        $expectedMessages = [
            'alnum' => ['notAlnum' => '\'[abc123]\' contains characters which are non alphabetic and no digits'],
        ];
        $result[] = [$entityName, $groupName, $value, $expectedResult, $expectedMessages];

        return $result;
    }

    /**
     * Check builder configuration format
     */
    public function testBuilderConfiguration()
    {
        $this->getMockBuilder(\Magento\Framework\Validator\Builder::class)->disableOriginalConstructor()->getMock();

        $this->_initConfig([__DIR__ . '/_files/validation/positive/builder/validation.xml']);
        $builder = $this->_config->createValidatorBuilder('test_entity_a', 'check_builder');
        $this->assertInstanceOf(\Magento\Framework\Validator\Builder::class, $builder);

        $expected = [
            [
                'alias' => '',
                'class' => \Magento\Framework\Validator\Test\Unit\Test\NotEmpty::class,
                'options' => null,
                'property' => 'int',
                'type' => 'property',
            ],
            [
                'alias' => 'stub',
                'class' => 'Validator_Stub',
                'options' => [
                    'arguments' => [
                        new \Magento\Framework\Validator\Constraint\Option('test_string_argument'),
                        new \Magento\Framework\Validator\Constraint\Option(
                            ['option1' => 'value1', 'option2' => 'value2']
                        ),
                        new \Magento\Framework\Validator\Constraint\Option\Callback(
                            [\Magento\Framework\Validator\Test\Unit\Test\Callback::class, 'getId'],
                            null,
                            true
                        ),
                    ],
                    'callback' => [
                        new \Magento\Framework\Validator\Constraint\Option\Callback(
                            [\Magento\Framework\Validator\Test\Unit\Test\Callback::class, 'configureValidator'],
                            null,
                            true
                        ),
                    ],
                    'methods' => [
                        'setOptionThree' => [
                            'method' => 'setOptionThree',
                            'arguments' => [
                                new \Magento\Framework\Validator\Constraint\Option(
                                    ['argOption' => 'argOptionValue']
                                ),
                                new \Magento\Framework\Validator\Constraint\Option\Callback(
                                    [\Magento\Framework\Validator\Test\Unit\Test\Callback::class, 'getId'],
                                    null,
                                    true
                                ),
                                new \Magento\Framework\Validator\Constraint\Option('10'),
                            ],
                        ],
                        'enableOptionFour' => ['method' => 'enableOptionFour'],
                    ],
                ],
                'property' => 'int',
                'type' => 'property'
            ],
        ];
        $this->assertAttributeEquals($expected, '_constraints', $builder);
    }

    /**
     * Check XSD schema validates invalid config files
     *
     * @dataProvider getInvalidXmlFiles
     * @expectedException \Magento\Framework\Exception\LocalizedException
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
        return [
            [__DIR__ . '/_files/validation/negative/no_constraint.xml'],
            [__DIR__ . '/_files/validation/negative/not_unique_use.xml'],
            [__DIR__ . '/_files/validation/negative/no_rule_for_reference.xml'],
            [__DIR__ . '/_files/validation/negative/no_name_for_entity.xml'],
            [__DIR__ . '/_files/validation/negative/no_name_for_rule.xml'],
            [__DIR__ . '/_files/validation/negative/no_name_for_group.xml'],
            [__DIR__ . '/_files/validation/negative/no_class_for_constraint.xml'],
            [__DIR__ . '/_files/validation/negative/invalid_method.xml'],
            [__DIR__ . '/_files/validation/negative/invalid_method_callback.xml'],
            [__DIR__ . '/_files/validation/negative/invalid_entity_callback.xml'],
            [__DIR__ . '/_files/validation/negative/invalid_child_for_option.xml'],
            [__DIR__ . '/_files/validation/negative/invalid_content_for_callback.xml'],
            [__DIR__ . '/_files/validation/negative/multiple_callback_in_argument.xml']
        ];
    }

    /**
     * Test schema file exists
     */
    public function testGetSchemaFile()
    {
        $this->_initConfig();
        $this->assertEquals(
            $this->urnResolver->getRealPath('urn:magento:framework:Validator/etc/validation.xsd'),
            $this->_config->getSchemaFile()
        );
        $this->assertFileExists($this->_config->getSchemaFile());
    }
}
