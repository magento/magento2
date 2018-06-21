<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator\Test\Unit;

/**
 * Class BuilderTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager
     */
    protected $_realObjectManager;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $config = new \Magento\Framework\ObjectManager\Config\Config(
            new \Magento\Framework\ObjectManager\Relations\Runtime()
        );
        $factory = new \Magento\Framework\ObjectManager\Factory\Dynamic\Developer($config);
        $this->_realObjectManager = new \Magento\Framework\ObjectManager\ObjectManager($factory, $config);
        $factory->setObjectManager($this->_realObjectManager);
    }

    /**
     * Test createValidator method
     *
     * @dataProvider createValidatorDataProvider
     *
     * @param array $constraints
     * @param \Magento\Framework\Validator\ValidatorInterface $expectedValidator
     */
    public function testCreateValidator(array $constraints, $expectedValidator)
    {
        /** @var $builder \Magento\Framework\Validator\Builder */
        $builder = $this->_objectManager->getObject(
            \Magento\Framework\Validator\Builder::class,
            [
                'constraintFactory' => new \Magento\Framework\Validator\ConstraintFactory($this->_realObjectManager),
                'validatorFactory' => new \Magento\Framework\ValidatorFactory($this->_realObjectManager),
                'oneValidatorFactory' => new \Magento\Framework\Validator\UniversalFactory($this->_realObjectManager),
                'constraints' => $constraints
            ]
        );
        $actualValidator = $builder->createValidator();
        $this->assertEquals($expectedValidator, $actualValidator);
    }

    /**
     * Data provider for
     *
     * @return array
     */
    public function createValidatorDataProvider()
    {
        $result = [];

        /** @var \Magento\Framework\Translate\AbstractAdapter $translator */
        $translator = $this->getMockBuilder(
            \Magento\Framework\Translate\AbstractAdapter::class
        )->getMockForAbstractClass();
        \Magento\Framework\Validator\AbstractValidator::setDefaultTranslator($translator);

        // Case 1. Check constructor with arguments
        $actualConstraints = [
            [
                'alias' => 'name_alias',
                'class' => \Magento\Framework\Validator\Test\Unit\Test\StringLength::class,
                'options' => [
                    'arguments' => [
                        'options' => ['min' => 1, 'max' => new \Magento\Framework\Validator\Constraint\Option(20)],
                    ],
                ],
                'property' => 'name',
                'type' => 'property',
            ],
        ];

        $expectedValidator = new \Magento\Framework\Validator();
        $expectedValidator->addValidator(
            new \Magento\Framework\Validator\Constraint\Property(
                new \Magento\Framework\Validator\Test\Unit\Test\StringLength(1, 20),
                'name',
                'name_alias'
            )
        );

        $result[] = [$actualConstraints, $expectedValidator];

        // Case 2. Check method calls
        $actualConstraints = [
            [
                'alias' => 'description_alias',
                'class' => \Magento\Framework\Validator\Test\Unit\Test\StringLength::class,
                'options' => [
                    'methods' => [
                        ['method' => 'setMin', 'arguments' => [10]],
                        ['method' => 'setMax', 'arguments' => [1000]],
                    ],
                ],
                'property' => 'description',
                'type' => 'property',
            ],
        ];

        $expectedValidator = new \Magento\Framework\Validator();
        $expectedValidator->addValidator(
            new \Magento\Framework\Validator\Constraint\Property(
                new \Magento\Framework\Validator\Test\Unit\Test\StringLength(10, 1000),
                'description',
                'description_alias'
            )
        );

        $result[] = [$actualConstraints, $expectedValidator];

        // Case 3. Check callback on validator
        $actualConstraints = [
        [
            'alias' => 'sku_alias',
            'class' => \Magento\Framework\Validator\Test\Unit\Test\StringLength::class,
        'options' => [
        'callback' => [
        new \Magento\Framework\Validator\Constraint\Option\Callback(
            function ($validator) {
                $validator->setMin(20);
                $validator->setMax(100);
            }
        ), ], ],'property' => 'sku', 'type' => 'property', ], ];

        $expectedValidator = new \Magento\Framework\Validator();
        $expectedValidator->addValidator(
            new \Magento\Framework\Validator\Constraint\Property(
                new \Magento\Framework\Validator\Test\Unit\Test\StringLength(20, 100),
                'sku',
                'sku_alias'
            )
        );

        $result[] = [$actualConstraints, $expectedValidator];

        return $result;
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
        /** @var $builder \Magento\Framework\Validator\Builder */
        $builder = $this->_objectManager->getObject(
            \Magento\Framework\Validator\Builder::class,
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
        /** @var $builder \Magento\Framework\Validator\Builder */
        $builder = $this->_objectManager->getObject(
            \Magento\Framework\Validator\Builder::class,
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
    public function configurationDataProvider()
    {
        $callback = new \Magento\Framework\Validator\Constraint\Option\Callback(
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
                [$this->_getExpectedConstraints($emptyConstraint, 'methods', [$someMethod])],
            ],
            'constraint options initialized with callback' => [
                [$emptyConstraint],
                'current_alias',
                $callbackConfig,
                [$this->_getExpectedConstraints($emptyConstraint, 'callback', [$callback])],
            ],
            'constraint options initialized with arguments' => [
                [$emptyConstraint],
                'current_alias',
                ['arguments' => ['some_argument' => 'some_value']],
                [
                    $this->_getExpectedConstraints(
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
                    $this->_getExpectedConstraints(
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
                [$this->_getExpectedConstraints($constraintWithArgs, 'methods', [$methodWithArgs])],
            ],
            'method added' => [
                [$configuredConstraint],
                'current_alias',
                $methodWithArgs,
                [
                    $this->_getExpectedConstraints(
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
                [$this->_getExpectedConstraints($constraintWithArgs, 'callback', [$callback])],
            ],
            'callback added' => [
                [$configuredConstraint],
                'current_alias',
                $callbackConfig,
                [$this->_getExpectedConstraints($configuredConstraint, 'callback', [$callback, $callback])],
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
    protected function _getExpectedConstraints($constraint, $optionKey, $optionValue)
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
        $this->_objectManager->getObject(\Magento\Framework\Validator\Builder::class, ['constraints' => $constraints]);
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
        /** @var $builder \Magento\Framework\Validator\Builder */
        $builder = $this->_objectManager->getObject(
            \Magento\Framework\Validator\Builder::class,
            ['constraints' => $constraints]
        );
        $builder->addConfiguration('alias', $options);
    }

    /**
     * Data provider for testing configuration validation
     *
     * @return array
     */
    public function invalidArgumentsDataProvider()
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
            \Magento\Framework\Validator\Builder::class,
            [
                'constraints' => [
                    ['alias' => 'alias', 'class' => 'StdClass', 'options' => null, 'type' => 'entity'],
                ],
                'validatorFactory' => new \Magento\Framework\ValidatorFactory($this->_realObjectManager)
            ]
        );
        $builder->createValidator();
    }

    /**
     * Test invalid configuration formats
     *
     * @dataProvider invalidConfigurationFormatDataProvider
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Configuration has incorrect format
     *
     * @param mixed $configuration
     */
    public function testAddConfigurationInvalidFormat($configuration)
    {
        $constraints = [
            ['alias' => 'alias', 'class' => 'Some\Validator\Class', 'options' => null, 'type' => 'entity'],
        ];
        /** @var $builder \Magento\Framework\Validator\Builder */
        $builder = $this->_objectManager->getObject(
            \Magento\Framework\Validator\Builder::class,
            ['constraints' => $constraints]
        );
        $builder->addConfigurations($configuration);
    }

    /**
     * Data provider for incorrect configurations
     *
     * @return array
     */
    public function invalidConfigurationFormatDataProvider()
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
