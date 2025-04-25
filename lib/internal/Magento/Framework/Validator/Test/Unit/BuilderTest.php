<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator\Test\Unit;

use Magento\Framework\ObjectManager\Config\Config;
use Magento\Framework\ObjectManager\Factory\Dynamic\Developer;
use Magento\Framework\ObjectManager\Relations\Runtime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate\AbstractAdapter;
use Magento\Framework\Validator;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Framework\Validator\Builder;
use Magento\Framework\Validator\Constraint\Option;
use Magento\Framework\Validator\Constraint\Option\Callback;
use Magento\Framework\Validator\Constraint\Property;
use Magento\Framework\Validator\ConstraintFactory;
use Magento\Framework\Validator\Test\Unit\Test\StringLength;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Framework\Validator\ValidatorInterface;
use Magento\Framework\ValidatorFactory;
use PHPUnit\Framework\TestCase;

/**
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BuilderTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager
     */
    protected $_realObjectManager;

    protected function setUp(): void
    {
        $this->_objectManager = new ObjectManager($this);

        $config = new Config(
            new Runtime()
        );
        $factory = new Developer($config);
        $this->_realObjectManager = new \Magento\Framework\ObjectManager\ObjectManager($factory, $config);
        $factory->setObjectManager($this->_realObjectManager);
    }

    /**
     * Test createValidator method
     *
     * @dataProvider createValidatorDataProvider
     *
     * @param array $constraint
     * string $property
     * int $min
     * int $max
     */
    public function testCreateValidator(array $constraints, string $property, int $min, int $max)
    {
        /** @var \Magento\Framework\Validator\Builder $builder */
        $builder = $this->_objectManager->getObject(
            Builder::class,
            [
                'constraintFactory' => new ConstraintFactory($this->_realObjectManager),
                'validatorFactory' => new ValidatorFactory($this->_realObjectManager),
                'oneValidatorFactory' => new UniversalFactory($this->_realObjectManager),
                'constraints' => $constraints
            ]
        );
        /** @var AbstractAdapter $translator */
        $translator = $this->getMockBuilder(
            AbstractAdapter::class
        )->getMockForAbstractClass();
        AbstractValidator::setDefaultTranslator($translator);

        $expectedValidator = new Validator();
        $expectedValidator->addValidator(
            new Property(
                new StringLength($min, $max),
                $property,
                $constraints[0]['alias']
            )
        );

        $actualValidator = $builder->createValidator();
        $this->assertEquals($expectedValidator, $actualValidator);
    }

    /**
     * Data provider for
     *
     * @return array
     */
    public static function createValidatorDataProvider()
    {
        return [
            [
                [
                    [
                        'alias' => 'name_alias',
                        'class' => StringLength::class,
                        'options' => [
                            'arguments' => [
                                'options' => ['min' => 1, 'max' => new Option(20)],
                            ],
                        ],
                        'property' => 'name',
                        'type' => 'property',
                    ]
                ],
                'name',
                1,
                20
            ],
            [
                [
                    [
                        'alias' => 'description_alias',
                        'class' => StringLength::class,
                        'options' => [
                            'methods' => [
                                ['method' => 'setMin', 'arguments' => [10]],
                                ['method' => 'setMax', 'arguments' => [1000]],
                            ],
                        ],
                        'property' => 'description',
                        'type' => 'property',
                    ]
                ],
                'description',
                10,
                1000
            ],
            [
                [
                    [
                        'alias' => 'sku_alias',
                        'class' => StringLength::class,
                        'options' => [
                            'methods' => [
                                ['method' => 'setMin', 'arguments' => [20]],
                                ['method' => 'setMax', 'arguments' => [100]],
                            ],
                        ],
                        'property' => 'sku',
                        'type' => 'property',
                    ]
                ],
                'sku',
                20,
                100
            ]
        ];
    }

    /**
     * Check addConfiguration logic
     *
     * @dataProvider configurationDataProvider
     *
     * @param array $constraints
     * @param string $alias
     * @param array $configuration
     * @param array $expected
     */
    public function testAddConfiguration($constraints, $alias, $configuration, $expected)
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        /** @var \Magento\Framework\Validator\Builder $builder */
        $builder = $this->_objectManager->getObject(
            Builder::class,
            ['constraints' => $constraints]
        );
        $builder->addConfiguration($alias, $configuration);
        $this->assertAttributeEquals($expected, '_constraints', $builder);
    }

    /**
     * Check addConfigurations logic
     *
     * @dataProvider configurationDataProvider
     *
     * @param array $constraints
     * @param string $alias
     * @param array $configuration
     * @param array $expected
     */
    public function testAddConfigurations($constraints, $alias, $configuration, $expected)
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        /** @var \Magento\Framework\Validator\Builder $builder */
        $builder = $this->_objectManager->getObject(
            Builder::class,
            ['constraints' => $constraints]
        );
        $configurations = [$alias => [$configuration]];
        $builder->addConfigurations($configurations);
        $this->assertAttributeEquals($expected, '_constraints', $builder);
    }

    /**
     * Builder configurations data provider
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function configurationDataProvider()
    {
        $callback = new Callback(
            [\Magento\Framework\Validator\Test\Unit\Test\Callback::class, 'getId']
        );
        $someMethod = ['method' => 'getMessages'];
        $methodWithArgs = ['method' => 'someMethod', 'arguments' => ['some_value_to_pass']];
        $callbackConfig = ['callback' => $callback];

        $configuredConstraint = [
            'alias' => 'current_alias',
            'class' => 'Some\Validator\Class',
            'options' => [
                'arguments' => ['some_argument' => 'some_value'],
                'callback' => [$callback],
                'methods' => [$someMethod],
            ],
            'property' => 'int',
            'type' => 'property',
        ];
        $emptyConstraint = [
            'alias' => 'current_alias',
            'class' => 'Some\Validator\Class',
            'options' => null,
            'property' => 'int',
            'type' => 'property',
        ];
        $constraintWithArgs = [
            'alias' => 'current_alias',
            'class' => 'Some\Validator\Class',
            'options' => ['arguments' => ['some_argument' => 'some_value']],
            'property' => 'int',
            'type' => 'property',
        ];
        return [
            'constraint is unchanged when alias not found' => [
                [$emptyConstraint],
                'some_alias',
                $someMethod,
                [$emptyConstraint],
            ],
            'constraint options initialized with method' => [
                [$emptyConstraint],
                'current_alias',
                $someMethod,
                [self::_getExpectedConstraints($emptyConstraint, 'methods', [$someMethod])],
            ],
            'constraint options initialized with callback' => [
                [$emptyConstraint],
                'current_alias',
                $callbackConfig,
                [self::_getExpectedConstraints($emptyConstraint, 'callback', [$callback])],
            ],
            'constraint options initialized with arguments' => [
                [$emptyConstraint],
                'current_alias',
                ['arguments' => ['some_argument' => 'some_value']],
                [
                    self::_getExpectedConstraints(
                        $emptyConstraint,
                        'arguments',
                        ['some_argument' => 'some_value']
                    )
                ],
            ],
            'constraint options arguments overwritten by newer arguments' => [
                [$configuredConstraint],
                'current_alias',
                ['arguments' => ['some_argument' => 'some_value']],
                [
                    self::_getExpectedConstraints(
                        $configuredConstraint,
                        'arguments',
                        ['some_argument' => 'some_value']
                    )
                ],
            ],
            'methods initialized' => [
                [$constraintWithArgs],
                'current_alias',
                $methodWithArgs,
                [self::_getExpectedConstraints($constraintWithArgs, 'methods', [$methodWithArgs])],
            ],
            'method added' => [
                [$configuredConstraint],
                'current_alias',
                $methodWithArgs,
                [
                    self::_getExpectedConstraints(
                        $configuredConstraint,
                        'methods',
                        [$someMethod, $methodWithArgs]
                    )
                ],
            ],
            'callback initialized' => [
                [$constraintWithArgs],
                'current_alias',
                $callbackConfig,
                [self::_getExpectedConstraints($constraintWithArgs, 'callback', [$callback])],
            ],
            'callback added' => [
                [$configuredConstraint],
                'current_alias',
                $callbackConfig,
                [self::_getExpectedConstraints($configuredConstraint, 'callback', [$callback, $callback])],
            ]
        ];
    }

    /**
     * Get expected constraint configuration by actual and changes
     *
     * @param array $constraint
     * @param string $optionKey
     * @param mixed $optionValue
     * @return array
     */
    protected static function _getExpectedConstraints($constraint, $optionKey, $optionValue)
    {
        if (!is_array($constraint['options'])) {
            $constraint['options'] = [];
        }
        $constraint['options'][$optionKey] = $optionValue;
        return $constraint;
    }

    /**
     * Check arguments validation passed into constructor
     *
     * @dataProvider invalidArgumentsDataProvider
     *
     * @param array $options
     * @param string $exception
     * @param string $exceptionMessage
     */
    public function testConstructorConfigValidation(array $options, $exception, $exceptionMessage)
    {
        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);
        if (array_key_exists('method', $options)) {
            $options = ['methods' => [$options]];
        }
        $constraints = [
            ['alias' => 'alias', 'class' => 'Some\Validator\Class', 'options' => $options, 'type' => 'entity'],
        ];
        $this->_objectManager->getObject(Builder::class, ['constraints' => $constraints]);
    }

    /**
     * Check arguments validation passed into configuration
     *
     * @dataProvider invalidArgumentsDataProvider
     *
     * @param array $options
     * @param string $exception
     * @param string $exceptionMessage
     */
    public function testAddConfigurationConfigValidation(array $options, $exception, $exceptionMessage)
    {
        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);

        $constraints = [
            ['alias' => 'alias', 'class' => 'Some\Validator\Class', 'options' => null, 'type' => 'entity'],
        ];
        /** @var \Magento\Framework\Validator\Builder $builder */
        $builder = $this->_objectManager->getObject(
            Builder::class,
            ['constraints' => $constraints]
        );
        $builder->addConfiguration('alias', $options);
    }

    /**
     * Data provider for testing configuration validation
     *
     * @return array
     */
    public static function invalidArgumentsDataProvider()
    {
        return [
            'constructor invalid arguments' => [
                ['arguments' => 'invalid_argument'],
                'InvalidArgumentException',
                'Arguments must be an array',
            ],
            'methods invalid arguments' => [
                ['method' => 'setValue', 'arguments' => 'invalid_argument'],
                'InvalidArgumentException',
                'Method arguments must be an array',
            ],
            'methods invalid format' => [
                ['method' => ['name' => 'setValue']],
                'InvalidArgumentException',
                'Method has to be passed as string',
            ],
            'constructor arguments invalid callback' => [
                ['callback' => ['invalid', 'callback']],
                'InvalidArgumentException',
                'Callback must be instance of \Magento\Framework\Validator\Constraint\Option\Callback',
            ]
        ];
    }

    /**
     * Check exception is thrown if validator is not an instance of \Magento\Framework\Validator\ValidatorInterface
     */
    public function testCreateValidatorInvalidInstance()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage(
            'Constraint class "StdClass" must implement \Magento\Framework\Validator\ValidatorInterface'
        );

        $builder = $this->_objectManager->getObject(
            Builder::class,
            [
                'constraints' => [
                    ['alias' => 'alias', 'class' => 'StdClass', 'options' => null, 'type' => 'entity'],
                ],
                'validatorFactory' => new ValidatorFactory($this->_realObjectManager)
            ]
        );
        $builder->createValidator();
    }

    /**
     * Test invalid configuration formats
     *
     * @dataProvider invalidConfigurationFormatDataProvider
     *
     *
     * @param mixed $configuration
     */
    public function testAddConfigurationInvalidFormat($configuration)
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Configuration has incorrect format');
        $constraints = [
            ['alias' => 'alias', 'class' => 'Some\Validator\Class', 'options' => null, 'type' => 'entity'],
        ];
        /** @var \Magento\Framework\Validator\Builder $builder */
        $builder = $this->_objectManager->getObject(
            Builder::class,
            ['constraints' => $constraints]
        );
        $builder->addConfigurations($configuration);
    }

    /**
     * Data provider for incorrect configurations
     *
     * @return array
     */
    public static function invalidConfigurationFormatDataProvider()
    {
        return [
            'configuration incorrect method call' => [
                ['alias' => ['method' => ['name' => 'incorrectMethodCall']]],
            ],
            'configuration incorrect configuration' => [
                ['alias' => [['data' => ['incorrectData']]]],
            ]
        ];
    }
}
