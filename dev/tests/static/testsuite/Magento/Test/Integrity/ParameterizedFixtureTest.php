<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Test\Integrity;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\TestFramework\Fixture\DataFixtureInterface;
use Magento\TestFramework\Utility\AddedFiles;
use Magento\TestFramework\Utility\ClassNameExtractor;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

/**
 * Static test for legacy data fixtures
 */
class ParameterizedFixtureTest extends TestCase
{
    private const array MODULES_WITH_FIXTURES = [
        'Magento\TestFramework\Fixture'
    ];

    /**
     * Validates parameterized data fixtures location
     *
     * @return void
     */
    public function testLocation(): void
    {
        $classNameExtractor = new ClassNameExtractor();
        $files = AddedFiles::getAddedFilesList(__DIR__ . '/..');
        $errors = [];
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'php' || !file_exists($file)) {
                continue;
            }
            $path = str_replace(BP . '/', '', $file);
            $errorMessage = "Parameterized data fixture $path MUST be placed in {{ModuleAppDir}}/Test/Fixture folder";
            $class = $classNameExtractor->getNameWithNamespace(file_get_contents($file));
            if ($class) {
                try {
                    $classReflection = new ReflectionClass($class);
                    if (!$classReflection->isSubclassOf(DataFixtureInterface::class)) {
                        continue;
                    }
                } catch (ReflectionException $exception) {
                    continue;
                }

                if (!$this->isLocationValid($file, $classReflection->getNamespaceName())) {
                    $errors[]  = $errorMessage;
                }
            }
        }
        if (!empty($errors)) {
            $this->fail(implode(PHP_EOL, $errors));
        }
    }

    /**
     * @param string $file
     * @param string $namespace
     * @return bool
     */
    private function isLocationValid(string $file, string $namespace): bool
    {
        return in_array($namespace, self::MODULES_WITH_FIXTURES)
            || (str_ends_with(dirname($file), '/Test/Fixture')
            && in_array(dirname($file, 3), (new ComponentRegistrar())->getPaths(ComponentRegistrar::MODULE)));
    }
}
