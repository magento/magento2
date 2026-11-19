<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Test\Workaround\Cleanup;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use Magento\TestFramework\Workaround\Cleanup\TestCaseProperties;

/**
 * Test class for \Magento\TestFramework\Workaround\Cleanup\TestCaseProperties.
 */
class TestCasePropertiesTest extends TestCase
{
    /**
     * @var array
     */
    protected $fixtureProperties = [
        'testPublic' => ['name' => 'testPublic', 'is_static' => false],
        '_testPrivate' => ['name' => '_testPrivate', 'is_static' => false],
        '_testPropertyBoolean' => ['name' => '_testPropertyBoolean', 'is_static' => false],
        '_testPropertyInteger' => ['name' => '_testPropertyInteger', 'is_static' => false],
        '_testPropertyFloat' => ['name' => '_testPropertyFloat', 'is_static' => false],
        '_testPropertyString' => ['name' => '_testPropertyString', 'is_static' => false],
        '_testPropertyArray' => ['name' => '_testPropertyArray', 'is_static' => false],
        'testPublicStatic' => ['name' => 'testPublicStatic', 'is_static' => true],
        '_testProtectedStatic' => ['name' => '_testProtectedStatic', 'is_static' => true],
        '_testPrivateStatic' => ['name' => '_testPrivateStatic', 'is_static' => true],
        'testStaticArray' => ['name' => 'testStaticArray', 'is_static' => true],
    ];

    /**
     * @return void
     */
    public function testEndTestSuiteClearsTestsFromSuiteList(): void
    {
        require_once __DIR__ . '/TestCasePropertiesTest/DummyTestCase.php';

        $dummy = new TestCasePropertiesTest\DummyTestCase('testDummy');
        $dummy->testDummy();
        $this->assertSame('public', $dummy->testPublic);

        $suite = TestSuite::empty('TestSuite');
        $suite->addTest($dummy);

        $clearProperties = new TestCaseProperties();
        $clearProperties->endTestSuite($suite);

        $this->assertFixturePropertiesCleared($dummy);
    }

    /**
     * @return void
     */
    public function testEndTestSuiteClearsTrackedCasesWhenSuiteTestsEmpty(): void
    {
        require_once __DIR__ . '/TestCasePropertiesTest/DummyTestCase.php';

        $dummy = new TestCasePropertiesTest\DummyTestCase('testDummy');
        $dummy->testDummy();
        $this->assertSame('public', $dummy->testPublic);

        $clearProperties = new TestCaseProperties();
        $clearProperties->startTest($dummy);
        $clearProperties->endTestSuite(TestSuite::empty(TestCasePropertiesTest\DummyTestCase::class));

        $this->assertFixturePropertiesCleared($dummy);
    }

    /**
     * Readonly and non-nullable typed properties must be left in place.
     *
     * @return void
     */
    public function testEndTestSuiteSkipsReadonlyAndNonNullableTypedProperties(): void
    {
        require_once __DIR__ . '/TestCasePropertiesTest/TypedDummyTestCase.php';

        $dummy = new TestCasePropertiesTest\TypedDummyTestCase('testDummy');
        $dummy->testDummy();
        $reflection = new \ReflectionClass($dummy);
        $typed = $reflection->getProperty('typed')->getValue($dummy);
        $fixed = $reflection->getProperty('fixed')->getValue($dummy);

        $suite = TestSuite::empty('TypedDummy');
        $suite->addTest($dummy);

        $clearProperties = new TestCaseProperties();
        $clearProperties->endTestSuite($suite);

        $this->assertSame($typed, $reflection->getProperty('typed')->getValue($dummy));
        $this->assertSame($fixed, $reflection->getProperty('fixed')->getValue($dummy));
        $this->assertNull($dummy->getNullable());
        $this->assertNull($reflection->getProperty('untyped')->getValue($dummy));
        $this->assertSame([], $dummy->getItems());
        $this->assertSame([], $reflection->getProperty('bareItems')->getValue($dummy));
    }

    /**
     * Static properties are not cleared here. StaticProperties owns that backup.
     *
     * @param string $propertyName
     * @return array|string
     */
    private function staticFixtureValue(string $propertyName): array|string
    {
        $values = [
            'testPublicStatic' => 'static public',
            '_testProtectedStatic' => 'static protected',
            '_testPrivateStatic' => 'static private',
            'testStaticArray' => ['kept'],
        ];

        return $values[$propertyName];
    }

    /**
     * @param TestCasePropertiesTest\DummyTestCase $dummy
     * @return void
     */
    private function assertFixturePropertiesCleared(TestCasePropertiesTest\DummyTestCase $dummy): void
    {
        $reflectionClass = new \ReflectionClass($dummy);
        foreach (array_keys($this->fixtureProperties) as $propertyName) {
            $property = $reflectionClass->getProperty($propertyName);
            if ($property->isStatic()) {
                $this->assertSame($this->staticFixtureValue($propertyName), $property->getValue());
            } else {
                $this->assertNull($property->getValue($dummy));
            }
        }
    }
}
