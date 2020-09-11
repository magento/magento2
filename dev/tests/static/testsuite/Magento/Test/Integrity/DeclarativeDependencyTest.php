<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Test\Integrity;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Utility\AggregateInvoker;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Exception\LocalizedException;
use Magento\Test\Integrity\Dependency\DeclarativeSchemaDependencyProvider;
use Magento\TestFramework\Inspection\Exception as InspectionException;
use PHPUnit\Framework\TestCase;

/**
 * Class DeclarativeDependencyTest
 * Test for undeclared dependencies in declarative schema
 */
class DeclarativeDependencyTest extends TestCase
{
    /**
     * @var DeclarativeSchemaDependencyProvider
     */
    private $dependencyProvider;

    /**
     * Sets up data
     *
     * @throws InspectionException
     */
    protected function setUp(): void
    {
        $root = BP;
        $rootJson = $this->readJsonFile($root . '/composer.json', true);
        if (preg_match('/magento\/project-*/', $rootJson['name']) == 1) {
            // The Dependency test is skipped for vendor/magento build
            self::markTestSkipped(
                'MAGETWO-43654: The build is running from vendor/magento. DependencyTest is skipped.'
            );
        }
        $objectManager = ObjectManager::getInstance();
        $this->dependencyProvider = $objectManager->create(DeclarativeSchemaDependencyProvider::class);
    }

    /**
     * @throws LocalizedException
     */
    public function testUndeclaredDependencies()
    {
        $invoker = new AggregateInvoker($this);
        $invoker(
            /**
             * Check undeclared modules dependencies for specified file
             *
             * @param string $fileType
             * @param string $file
             */
            function ($file) {
                $componentRegistrar = new ComponentRegistrar();
                $foundModuleName = '';
                foreach ($componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleName => $moduleDir) {
                    if (strpos($file, $moduleDir . '/') !== false) {
                        $foundModuleName = str_replace('_', '\\', $moduleName);
                        break;
                    }
                }
                if (empty($foundModuleName)) {
                    return;
                }

                $undeclaredDependency = $this->dependencyProvider->getUndeclaredModuleDependencies($foundModuleName);

                $result = [];
                foreach ($undeclaredDependency as $name => $modules) {
                    $modules = array_unique($modules);
                    $result[] = $this->getErrorMessage($name) . "\n" . implode("\t\n", $modules) . "\n";
                }
                if (!empty($result)) {
                    $this->fail(
                        'Module ' . $moduleName . ' has undeclared dependencies: ' . "\n" . implode("\t\n", $result)
                    );
                }
            },
            $this->prepareFiles(Files::init()->getDbSchemaFiles())
        );
    }

    /**
     * Convert file list to data provider structure.
     *
     * @param string[] $files
     * @return array
     */
    private function prepareFiles(array $files): array
    {
        $result = [];
        foreach ($files as $relativePath => $file) {
            $absolutePath = reset($file);
            $result[$relativePath] = [$absolutePath];
        }
        return $result;
    }

    /**
     * Retrieve error message for dependency.
     *
     * @param string $id
     * @return string
     */
    private function getErrorMessage(string $id): string
    {
        $decodedId = DeclarativeSchemaDependencyProvider::decodeDependencyId($id);
        $entityType = $decodedId['entityType'];
        if ($entityType === DeclarativeSchemaDependencyProvider::SCHEMA_ENTITY_TABLE) {
            $message = sprintf(
                'Table %s has undeclared dependency on one of the following modules:',
                $decodedId['tableName']
            );
        } else {
            $message = sprintf(
                '%s %s from %s table has undeclared dependency on one of the following modules:',
                ucfirst($entityType),
                $decodedId['entityName'],
                $decodedId['tableName']
            );
        }

        return $message;
    }

    /**
     * Read data from json file.
     *
     * @param string $file
     * @return mixed
     * @throws InspectionException
     */
    private function readJsonFile(string $file, bool $asArray = false)
    {
        $decodedJson = json_decode(file_get_contents($file), $asArray);
        if (null == $decodedJson) {
            throw new InspectionException("Invalid Json: $file");
        }

        return $decodedJson;
    }
}
