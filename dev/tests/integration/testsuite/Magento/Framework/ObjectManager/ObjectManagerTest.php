<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager;

class ObjectManagerTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Test class with type error
     */
    const TEST_CLASS_WITH_TYPE_ERROR = \Magento\Framework\ObjectManager\TestAsset\ConstructorWithTypeError::class;

    /**#@+
     * Test classes for basic instantiation
     */
    const TEST_CLASS = \Magento\Framework\ObjectManager\TestAsset\Basic::class;

    const TEST_CLASS_INJECTION = \Magento\Framework\ObjectManager\TestAsset\BasicInjection::class;

    /**#@-*/

    /**#@+
     * Test classes and interface to test preferences
     */
    const TEST_INTERFACE = \Magento\Framework\ObjectManager\TestAsset\TestAssetInterface::class;

    const TEST_INTERFACE_IMPLEMENTATION = \Magento\Framework\ObjectManager\TestAsset\InterfaceImplementation::class;

    const TEST_CLASS_WITH_INTERFACE = \Magento\Framework\ObjectManager\TestAsset\InterfaceInjection::class;

    /**#@-*/

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected static $_objectManager;

    /**
     * List of classes with different number of arguments
     *
     * @var array
     */
    protected $_numerableClasses = [
        0 => \Magento\Framework\ObjectManager\TestAsset\ConstructorNoArguments::class,
        1 => \Magento\Framework\ObjectManager\TestAsset\ConstructorOneArgument::class,
        2 => \Magento\Framework\ObjectManager\TestAsset\ConstructorTwoArguments::class,
        3 => \Magento\Framework\ObjectManager\TestAsset\ConstructorThreeArguments::class,
        4 => \Magento\Framework\ObjectManager\TestAsset\ConstructorFourArguments::class,
        5 => \Magento\Framework\ObjectManager\TestAsset\ConstructorFiveArguments::class,
        6 => \Magento\Framework\ObjectManager\TestAsset\ConstructorSixArguments::class,
        7 => \Magento\Framework\ObjectManager\TestAsset\ConstructorSevenArguments::class,
        8 => \Magento\Framework\ObjectManager\TestAsset\ConstructorEightArguments::class,
        9 => \Magento\Framework\ObjectManager\TestAsset\ConstructorNineArguments::class,
        10 => \Magento\Framework\ObjectManager\TestAsset\ConstructorTenArguments::class,
    ];

    /**
     * Names of properties
     *
     * @var array
     */
    protected $_numerableProperties = [
        1 => '_one',
        2 => '_two',
        3 => '_three',
        4 => '_four',
        5 => '_five',
        6 => '_six',
        7 => '_seven',
        8 => '_eight',
        9 => '_nine',
        10 => '_ten',
    ];

    public static function setUpBeforeClass()
    {
        $config = new \Magento\Framework\ObjectManager\Config\Config();
        $factory = new Factory\Dynamic\Developer($config);

        self::$_objectManager = new \Magento\Framework\ObjectManager\ObjectManager($factory, $config);
        self::$_objectManager->configure(
            ['preferences' => [self::TEST_INTERFACE => self::TEST_INTERFACE_IMPLEMENTATION]]
        );
        $factory->setObjectManager(self::$_objectManager);
    }

    public static function tearDownAfterClass()
    {
        self::$_objectManager = null;
    }

    /**
     * Data provider for testNewInstance
     *
     * @return array
     */
    public function newInstanceDataProvider()
    {
        $data = [
            'basic model' => [
                '$actualClassName' => self::TEST_CLASS_INJECTION,
                '$properties' => ['_object' => self::TEST_CLASS],
            ],
            'model with interface' => [
                '$actualClassName' => self::TEST_CLASS_WITH_INTERFACE,
                '$properties' => ['_object' => self::TEST_INTERFACE_IMPLEMENTATION],
            ],
        ];

        foreach ($this->_numerableClasses as $number => $className) {
            $properties = [];
            for ($i = 1; $i <= $number; $i++) {
                $propertyName = $this->_numerableProperties[$i];
                $properties[$propertyName] = self::TEST_CLASS;
            }
            $data[$number . ' arguments'] = ['$actualClassName' => $className, '$properties' => $properties];
        }

        return $data;
    }

    /**
     * @param string $actualClassName
     * @param array $properties
     * @param string|null $expectedClassName
     *
     * @dataProvider newInstanceDataProvider
     */
    public function testNewInstance($actualClassName, array $properties = [], $expectedClassName = null)
    {
        if (!$expectedClassName) {
            $expectedClassName = $actualClassName;
        }

        $testObject = self::$_objectManager->create($actualClassName);
        $this->assertInstanceOf($expectedClassName, $testObject);

        if ($properties) {
            foreach ($properties as $propertyName => $propertyClass) {
                $this->assertAttributeInstanceOf($propertyClass, $propertyName, $testObject);
            }
        }
    }

    /**
     * Test creating an object and passing incorrect type of arguments to the constructor.
     *
     * @expectedException \Magento\Framework\Exception\RuntimeException
     * @expectedExceptionMessage Error occurred when creating object
     */
    public function testNewInstanceWithTypeError()
    {
        self::$_objectManager->create(self::TEST_CLASS_WITH_TYPE_ERROR, [
            'testArgument' => new \stdClass()
        ]);
    }
}
