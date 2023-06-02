<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager;

use Magento\Framework\App\Utility\Classes;
use Magento\Framework\ObjectManager\FactoryInterface as ObjectManagerFactoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\GraphQl\App\State\Collector;
use Magento\GraphQl\App\State\Comparator;

/**
 * Test that verifies that resetState method for classes cause the state to be the same as it was initially constructed
 */
class ResetAfterRequestTest extends \PHPUnit\Framework\TestCase
{

    private static $objectManager;

    private ?Comparator $comparator;
    private ?Collector $collector;

    public static function setUpBeforeClass(): void
    {
        $config = new \Magento\Framework\ObjectManager\Config\Config();
        $factory = new Factory\Dynamic\Developer($config);
        self::$objectManager = new \Magento\Framework\ObjectManager\ObjectManager($factory, $config);
//        self::$objectManager->configure(
//            ['preferences' => [self::TEST_INTERFACE => self::TEST_INTERFACE_IMPLEMENTATION]]
//        );
        $factory->setObjectManager(self::$objectManager);
    }

    public static function tearDownAfterClass(): void
    {
        self::$objectManager = null;
    }

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->comparator = static::$objectManager->create(Comparator::class);
        $this->collector = static::$objectManager->create(Collector::class);
    }

    /**
     * Data provider for testNewInstance
     *
     * Provides list of all classes and virtual classes that implement ResetAfterRequestInterface
     *
     * @return array
     */
    public function resetAfterRequestClassDataProvider()
    {
        $resetAfterRequestClasses = [];
        foreach (Classes::getVirtualClasses() as $name => $type ) {
            try {
                if (!class_exists($type)) {
                    continue;
                }
                if (is_a($type, ObjectManagerInterface::class)) {
                    continue;
                }
                if (is_a($type, ObjectManagerFactoryInterface::class)) {
                    continue;
                }
                if (is_a($type, ResetAfterRequestInterface::class, true)) {
                    $resetAfterRequestClasses[] = [$name];
                }
            } catch (\Error $error) {
                continue;
            }
        }
        foreach (array_keys(Classes::collectModuleClasses('[A-Z][a-z\d][A-Za-z\d\\\\]+')) as $type) {
            try {
                if (!class_exists($type)) {
                    continue;
                }
                if (is_a($type, ObjectManagerInterface::class)) {
                    continue;
                }
                if (is_a($type, ObjectManagerFactoryInterface::class)) {
                    continue;
                }
                if (is_a($type, ResetAfterRequestInterface::class, true)) {
                    $resetAfterRequestClasses[] = [$type];
                }
            } catch (\Throwable $throwable) {
                continue;
            }
        }
        return $resetAfterRequestClasses;
    }

    /**
     * Verifies that resetState method for classes cause the state to be the same as it was initially constructed
     *
     * @param string $className
     * @dataProvider resetAfterRequestClassDataProvider
     */
    public function testResetAfterRequestClasses(string $className)
    {
        /** @var ResetAfterRequestInterface $object */
        $object = self::$objectManager->get($className);
        $beforeProperties = $this->collector->getPropertiesFromObject($object);
        $object->_resetState();
        $afterProperties = $this->collector->getPropertiesFromObject($object);
        $differences = [];
        foreach ($afterProperties as $propertyName => $propertyValue) {
            $result = $this->comparator->checkValues($beforeProperties[$propertyName] ?? null, $propertyValue);
            if ($result) {
                $differences[$propertyName] = $result;
            }
        }
        $this->assertEmpty($differences, var_export($differences, true));
    }
}
