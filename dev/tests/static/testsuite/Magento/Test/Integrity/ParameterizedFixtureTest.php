<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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

                if (!$this->isFileLocatedInModuleDirectory($file)) {
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
     * @return bool
     */
    private function isFileLocatedInModuleDirectory(string $file): bool
    {
        $componentRegistrar = new ComponentRegistrar();
        $found = false;
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleDir) {
            if ($file === $moduleDir . '/Test/Fixture/' . basename($file)) {
                $found = true;
                break;
            }
        }
        return $found;
    }
}
