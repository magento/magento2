<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator\Test\Unit;

use Magento\Framework\App\Arguments\ValidationState;
use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Config\DomFactory;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManager\Factory\Dynamic\Developer;
use Magento\Framework\ObjectManager\Relations\Runtime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Framework\Validator\Builder;
use Magento\Framework\Validator\Config;
use Magento\Framework\Validator\Constraint\Option;
use Magento\Framework\Validator\Constraint\Option\Callback;
use Magento\Framework\Validator\Test\Unit\Test\NotEmpty;
use Magento\Framework\Validator\UniversalFactory;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    protected $_config;

    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /** @var UrnResolver */
    protected $urnResolver;

    protected function setUp(): void
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $this->_objectManager = new ObjectManager($this);
        $this->urnResolver = new UrnResolver();
    }

    public function testConstructException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('There must be at least one configuration file specified.');
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
            new Runtime()
        );
        $factory = new Developer($config);
        $appObjectManager = new \Magento\Framework\ObjectManager\ObjectManager($factory, $config);
        $factory->setObjectManager($appObjectManager);
        /** @var UniversalFactory $universalFactory */
        $universalFactory = $appObjectManager->get(UniversalFactory::class);
        /** @var UrnResolver $urnResolverMock */
        $urnResolverMock = $this->createMock(UrnResolver::class);
        $urnResolverMock->expects($this->any())
            ->method('getRealPath')
            ->with('urn:magento:framework:Validator/etc/validation.xsd')
            ->willReturn($this->urnResolver->getRealPath('urn:magento:framework:Validator/etc/validation.xsd'));
        $appObjectManager->configure(
            [
                'preferences' => [
                    ValidationStateInterface::class => ValidationState::class,
                ],
                ValidationState::class => [
                    'arguments' => [
                        'appMode' => 'developer',
                    ]
                ]
            ]
        );
        $this->_config = $this->_objectManager->getObject(
            Config::class,
            [
                'configFiles' => $configFiles,
                'builderFactory' => $universalFactory,
                'domFactory' => new DomFactory($appObjectManager),
                'urnResolver' => $urnResolverMock
            ]
        );
    }

    public function testCreateValidatorInvalidEntityName()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Unknown validation entity "invalid_entity"');
        $this->_initConfig();
        $this->_config->createValidatorBuilder('invalid_entity', null);
    }

    public function testCreateValidatorInvalidGroupName()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Unknown validation group "invalid_group" in entity "test_entity_a"');
        $this->_initConfig();
        $this->_config->createValidatorBuilder('test_entity_a', 'invalid_group');
    }

    public function testCreateValidatorInvalidConstraintClass()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage(
            'Constraint class "stdClass" must implement \Magento\Framework\Validator\ValidatorInterface'
        );
        $this->_initConfig([__DIR__ . '/_files/validation/negative/invalid_constraint.xml']);
        $this->_config->createValidator('test_entity', 'test_group');
    }

    public function testGetValidatorBuilderClassNotFound()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Builder class "UnknownBuilderClass" was not found');
        $this->_initConfig([__DIR__ . '/_files/validation/negative/invalid_builder_class.xml']);
        $this->_config->createValidatorBuilder('catalog_product', 'create');
    }

    public function testGetValidatorBuilderInstanceInvalid()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Builder "stdClass" must extend \Magento\Framework\Validator\Builder');
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
        $this->assertInstanceOf(Builder::class, $builder);
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
        AbstractValidator::setDefaultTranslator();
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
        $value = new DataObject(['int' => 1, 'alnum' => 'abc123']);
        $expectedResult = true;
        $expectedMessages = [];
        $result[] = [$entityName, $groupName, $value, $expectedResult, $expectedMessages];

        // Case 2. Fail check alnum is not empty
        $value = new DataObject(['int' => 'abc123', 'alnum' => null]);
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
        $value = new DataObject(['int' => 'abc123', 'alnum' => 'abc123']);
        $expectedResult = true;
        $expectedMessages = [];
        $result[] = [$entityName, $groupName, $value, $expectedResult, $expectedMessages];

        // Case 4. Fail check alnum has valid value
        $value = new DataObject(['int' => 'abc123', 'alnum' => '[abc123]']);
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
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_initConfig([__DIR__ . '/_files/validation/positive/builder/validation.xml']);
        $builder = $this->_config->createValidatorBuilder('test_entity_a', 'check_builder');
        $this->assertInstanceOf(Builder::class, $builder);

        $expected = [
            [
                'alias' => '',
                'class' => NotEmpty::class,
                'options' => null,
                'property' => 'int',
                'type' => 'property',
            ],
            [
                'alias' => 'stub',
                'class' => 'Validator_Stub',
                'options' => [
                    'arguments' => [
                        new Option('test_string_argument'),
                        new Option(
                            ['option1' => 'value1', 'option2' => 'value2']
                        ),
                        new Callback(
                            [\Magento\Framework\Validator\Test\Unit\Test\Callback::class, 'getId'],
                            null,
                            true
                        ),
                    ],
                    'callback' => [
                        new Callback(
                            [\Magento\Framework\Validator\Test\Unit\Test\Callback::class, 'configureValidator'],
                            null,
                            true
                        ),
                    ],
                    'methods' => [
                        'setOptionThree' => [
                            'method' => 'setOptionThree',
                            'arguments' => [
                                new Option(
                                    ['argOption' => 'argOptionValue']
                                ),
                                new Callback(
                                    [\Magento\Framework\Validator\Test\Unit\Test\Callback::class, 'getId'],
                                    null,
                                    true
                                ),
                                new Option('10'),
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
     *
     * @param array|string $configFile
     */
    public function testValidateInvalidConfigFiles($configFile)
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
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
