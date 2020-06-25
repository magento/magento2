<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Test\Integrity\Dependency;

use Magento\Framework\GraphQlSchemaStitching\GraphQlReader;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\TypeReaderComposite;

/**
 * Provide information on the dependency between the modules according to the GraphQL schema.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class GraphQlSchemaDependencyProvider
{
    /**
     * @var array
     */
    private $parsedSchema = [];

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * GraphQlSchemaDependencyProvider constructor.
     * @param \Magento\Test\Integrity\Dependency\DependencyProvider\Proxy $dependencyProvider
     */
    public function __construct(\Magento\Test\Integrity\Dependency\DependencyProvider\Proxy $dependencyProvider)
    {
        $this->dependencyProvider = $dependencyProvider;
        $this->getGraphQlSchemaDeclaration();
    }

    /**
     * Provide declared dependencies between modules based on the declarative schema configuration.
     *
     * @param string $moduleName
     * @return array
     * @throws \Magento\TestFramework\Inspection\Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDeclaredExistingModuleDependencies(string $moduleName): array
    {
        $dependencies = $this->getDependenciesFromSchema($moduleName);
        $declared = $this->dependencyProvider->getDeclaredDependencies(
            $moduleName,
            DependencyProvider::TYPE_HARD,
            DependencyProvider::MAP_TYPE_DECLARED
        );
        return array_unique(array_values(array_intersect($declared, $dependencies)));
    }

    /**
     * Provide undeclared dependencies between modules based on the declarative schema configuration.
     *
     * [
     *     $dependencyId => [$module1, $module2, $module3 ...],
     *     ...
     * ]
     *
     * @param string $moduleName
     * @return array
     * @throws \Magento\TestFramework\Inspection\Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getUndeclaredModuleDependencies(string $moduleName): array
    {
        $dependencies = $this->getDependenciesFromSchema($moduleName);
        return $this->collectDependencies($moduleName, $dependencies);
    }

    /**
     * Get parsed GraphQl schema
     *
     * @return array
     */
    private function getGraphQlSchemaDeclaration(): array
    {
        if (!$this->parsedSchema) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $typeReader = $objectManager->create(TypeReaderComposite::class);
            $reader = $objectManager->create(GraphQlReader::class, ['typeReader' => $typeReader]);
            $this->parsedSchema = $reader->read();
        }

        return $this->parsedSchema;
    }

    /**
     * Get dependencies from GraphQl schema
     *
     * @param string $moduleName
     * @return array
     */
    private function getDependenciesFromSchema(string $moduleName): array
    {
        $schema = $this->parsedSchema;

        $dependencies = [];

        foreach ($schema as $type) {
            if (isset($type['module']) && $type['module'] == $moduleName && isset($type['implements'])) {
                $interfaces = array_keys($type['implements']);
                foreach ($interfaces as $interface) {
                    $dependOnModule = $schema[$interface]['module'];
                    if ($dependOnModule != $moduleName) {
                        $dependencies[] = $dependOnModule;
                    }
                }

            }
        }
        return array_unique($dependencies);
    }

    /**
     * Collect module dependencies.
     *
     * @param string $currentModuleName
     * @param array $dependencies
     * @return array
     */
    private function collectDependencies(string $currentModuleName, array $dependencies = []): array
    {
        if (empty($dependencies)) {
            return [];
        }
        $declared = $this->dependencyProvider->getDeclaredDependencies(
            $currentModuleName,
            DependencyProvider::TYPE_HARD,
            DependencyProvider::MAP_TYPE_DECLARED
        );
        $checkResult = array_intersect($declared, $dependencies);

        if (empty($checkResult)) {
            $this->dependencyProvider->addDependencies(
                $currentModuleName,
                DependencyProvider::TYPE_HARD,
                DependencyProvider::MAP_TYPE_FOUND,
                [$currentModuleName => $dependencies]
            );
        }

        return $this->dependencyProvider->getDeclaredDependencies(
            $currentModuleName,
            DependencyProvider::TYPE_HARD,
            DependencyProvider::MAP_TYPE_FOUND
        );
    }
}
