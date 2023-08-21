<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Integrity\Library;

use Laminas\Code\Reflection\ClassReflection;
use PHPUnit\Framework\TestCase;
use ReflectionException;

require_once __DIR__ . '/_files/DummyInjectableClass.php';

/**
 * Test for Magento\TestFramework\Integrity\Library\Injectable
 */
class InjectableTest extends TestCase
{
    /**
     * Covered getDependencies
     *
     * @return void
     * @throws ReflectionException
     */
    public function testGetDependencies(): void
    {
        $injectable = new Injectable();
        $classReflection = new ClassReflection(DummyInjectableClass::class);

        $actualResult = $injectable->getDependencies($classReflection);
        $expectedResult = [
            'Magento\Framework\DataObject',
            'TestNamespace\Some\SomeTestClass',
            'TestNamespace\Other\Test',
        ];

        $this->assertEquals($expectedResult, $actualResult);
    }
}
