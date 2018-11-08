<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Test\Integrity;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Setup\Declaration\Schema\Config\Converter;

/**
 * Class DeclarativeDependencyTest
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class DeclarativeDependencyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Types of dependency between modules.
     */
    const TYPE_HARD = 'hard';

    /**
     * The identifier of dependency for mapping.
     */
    const MAP_TYPE_DECLARED = 'declared';

    /**
     * The identifier of dependency for mapping.
     */
    const MAP_TYPE_FOUND = 'found';

    /**
     * Declarative name for table entity of the declarative schema.
     */
    const SCHEMA_ENTITY_TABLE = 'table';

    /**
     * Declarative name for column entity of the declarative schema.
     */
    const SCHEMA_ENTITY_COLUMN = 'column';

    /**
     * Declarative name for constraint entity of the declarative schema.
     */
    const SCHEMA_ENTITY_CONSTRAINT = 'constraint';

    /**
     * Declarative name for index entity of the declarative schema.
     */
    const SCHEMA_ENTITY_INDEX = 'index';

    /**
     * @var array
     */
    private $mapDependencies = [];

    /**
     * @var array
     */
    private $dbSchemaDeclaration = [];

    /**
     * @var array
     */
    private $packageModuleMapping = [];

    /**
     * Sets up data
     *
     * @throws \Exception
     */
    protected function setUp()
    {
        $root = BP;
        $rootJson = $this->readJsonFile($root . '/composer.json', true);
        if (preg_match('/magento\/project-*/', $rootJson['name']) == 1) {
            // The Dependency test is skipped for vendor/magento build
            self::markTestSkipped(
                'MAGETWO-43654: The build is running from vendor/magento. DependencyTest is skipped.'
            );
        }
    }

    /**
     * Initialise map of dependencies.
     *
     * @throws \Exception
     */
    private function initDeclaredDependencies()
    {
        $jsonFiles = Files::init()->getComposerFiles(ComponentRegistrar::MODULE, false);
        foreach ($jsonFiles as $file) {
            $json = new \Magento\Framework\Config\Composer\Package($this->readJsonFile($file));
            $moduleName = $this->convertModuleName($json->get('name'));
            $require = array_keys((array)$json->get('require'));
            $this->presetDependencies($moduleName, $require, self::TYPE_HARD);
        }
    }

    /**
     * Read data from json file.
     *
     * @param string $file
     * @return mixed
     * @throws \Exception
     */
    private function readJsonFile(string $file, bool $asArray = false)
    {
        $decodedJson = json_decode(file_get_contents($file), $asArray);
        if (null == $decodedJson) {
            throw new \Exception("Invalid Json: $file");
        }

        return $decodedJson;
    }

    /**
     * @throws \Exception
     */
    public function testUndeclaredDependencies()
    {
        $this->initDeclaredDependencies();
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
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

                $dependencies = $this->getDependenciesFromFiles($file);
                $dependencies = $this->filterSelfDependency($foundModuleName, $dependencies);
                $undeclaredDependency = $this->collectDependencies($foundModuleName, $dependencies);

                $result = [];
                foreach ($undeclaredDependency as $name => $modules) {
                    $modules = array_unique($modules);
                    $result[] = $this->getErrorMessage($name) . "\n" . implode("\t\n", $modules);
                }
                if (count($result)) {
                    $this->fail(
                        'Module ' . $moduleName . ' has undeclared dependencies: ' . "\n" . implode("\t\n", $result)
                    );
                }
            },
            $this->prepareFiles(Files::init()->getDbSchemaFiles())
        );
    }

    /**
     * Remove self dependencies.
     *
     * @param string $moduleName
     * @param array $dependencies
     * @return array
     */
    private function filterSelfDependency(string $moduleName, array $dependencies):array
    {
        foreach ($dependencies as $id => $modules) {
            $decodedId = $this->decodeDependencyId($id);
            $entityType = $decodedId['entityType'];
            if ($entityType === self::SCHEMA_ENTITY_TABLE || $entityType === "column") {
                if (array_search($moduleName, $modules) !== false) {
                    unset($dependencies[$id]);
                }
            } else {
                $dependencies[$id] = $this->filterComplexDependency($moduleName, $modules);
            }
        }

        return array_filter($dependencies);
    }

    /**
     * Remove already declared dependencies.
     *
     * @param string $moduleName
     * @param array $modules
     * @return array
     */
    private function filterComplexDependency(string $moduleName, array $modules): array
    {
        $resultDependencies = [];
        if (!is_array(reset($modules))) {
            if (array_search($moduleName, $modules) === false) {
                $resultDependencies = $modules;
            }
        } else {
            foreach ($modules as $dependencySet) {
                if (array_search($moduleName, $dependencySet) === false) {
                    $resultDependencies = array_merge(
                        $resultDependencies,
                        $dependencySet
                    );
                }
            }
        }

        return array_values(array_unique($resultDependencies));
    }

    /**
     * Retrieve declarative schema declaration.
     *
     * @return array
     * @throws \Exception
     */
    private function getDeclarativeSchema(): array
    {
        if ($this->dbSchemaDeclaration) {
            return $this->dbSchemaDeclaration;
        }

        $entityTypes = [self::SCHEMA_ENTITY_COLUMN, self::SCHEMA_ENTITY_CONSTRAINT, self::SCHEMA_ENTITY_INDEX];
        $declaration = [];
        foreach (Files::init()->getDbSchemaFiles() as $filePath) {
            $filePath = reset($filePath);
            preg_match('#app/code/(\w+/\w+)#', $filePath, $result);
            $moduleName = str_replace('/', '\\', $result[1]);
            $moduleDeclaration = $this->getDbSchemaDeclaration($filePath);

            foreach ($moduleDeclaration[self::SCHEMA_ENTITY_TABLE] as $tableName => $tableDeclaration) {
                if (!isset($tableDeclaration['modules'])) {
                    $tableDeclaration['modules'] = [];
                }
                array_push($tableDeclaration['modules'], $moduleName);
                $moduleDeclaration = array_replace_recursive(
                    $moduleDeclaration,
                    [self::SCHEMA_ENTITY_TABLE =>
                        [
                            $tableName => $tableDeclaration,
                        ]
                    ]
                );
                foreach ($entityTypes as $entityType) {
                    if (!isset($tableDeclaration[$entityType])) {
                        continue;
                    }
                    $moduleDeclaration = array_replace_recursive(
                        $moduleDeclaration,
                        [self::SCHEMA_ENTITY_TABLE =>
                            [
                                $tableName =>
                                    $this->addModuleAssigment($tableDeclaration, $entityType, $moduleName)
                                ]
                            ]
                    );
                }
            }
            $declaration = array_merge_recursive($declaration, $moduleDeclaration);
        }
        $this->dbSchemaDeclaration = $declaration;

        return $this->dbSchemaDeclaration;
    }

    /**
     * Get declared dependencies.
     *
     * @param string $tableName
     * @param string $entityType
     * @param null|string $entityName
     * @return array
     * @throws \Exception
     */
    private function resolveEntityDependencies(string $tableName, string $entityType, ?string $entityName = null): array
    {
        switch ($entityType) {
            case self::SCHEMA_ENTITY_COLUMN:
            case self::SCHEMA_ENTITY_CONSTRAINT:
            case self::SCHEMA_ENTITY_INDEX:
                return $this->getDeclarativeSchema()
                    [self::SCHEMA_ENTITY_TABLE][$tableName][$entityType][$entityName]['modules'];
                break;
            case self::SCHEMA_ENTITY_TABLE:
                return $this->getDeclarativeSchema()[self::SCHEMA_ENTITY_TABLE][$tableName]['modules'];
                break;
            default:
                return [];
        }
    }

    /**
     * @param string $filePath
     * @return array
     */
    private function getDbSchemaDeclaration(string $filePath): array
    {
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($filePath));
        return (new Converter())->convert($dom);
    }

    /**
     * Add dependency on the current module.
     *
     * @param array $tableDeclaration
     * @param string $entityType
     * @param string $moduleName
     * @return array
     */
    private function addModuleAssigment(
        array $tableDeclaration,
        string $entityType,
        string $moduleName
    ): array {
        $declarationWithAssigment = [];
        foreach ($tableDeclaration[$entityType] as $entityName => $entityDeclaration) {
            if (!isset($entityDeclaration['modules'])) {
                $entityDeclaration['modules'] = [];
            }
            if (!$this->isEntityDisabled($entityDeclaration)) {
                array_push($entityDeclaration['modules'], $moduleName);
            }

            $declarationWithAssigment[$entityType][$entityName] = $entityDeclaration;
        }

        return $declarationWithAssigment;
    }

    /**
     * Retrieve dependencies from files.
     *
     * @param string $file
     * @return string[]
     * @throws \Exception
     */
    private function getDependenciesFromFiles($file)
    {
        $moduleDbSchema = $this->getDbSchemaDeclaration($file);
        $dependencies = array_merge_recursive(
            $this->getDisabledDependencies($moduleDbSchema),
            $this->getConstraintDependencies($moduleDbSchema),
            $this->getIndexDependencies($moduleDbSchema)
        );
        return $dependencies;
    }

    /**
     * Retrieve dependencies for disabled entities.
     *
     * @param array $moduleDeclaration
     * @return array
     * @throws \Exception
     */
    private function getDisabledDependencies(array $moduleDeclaration): array
    {
        $disabledDependencies = [];
        $entityTypes = [self::SCHEMA_ENTITY_COLUMN, self::SCHEMA_ENTITY_CONSTRAINT, self::SCHEMA_ENTITY_INDEX];
        foreach ($moduleDeclaration[self::SCHEMA_ENTITY_TABLE] as $tableName => $tableDeclaration) {
            foreach ($entityTypes as $entityType) {
                if (!isset($tableDeclaration[$entityType])) {
                    continue;
                }
                foreach ($tableDeclaration[$entityType] as $entityName => $entityDeclaration) {
                    if ($this->isEntityDisabled($entityDeclaration)) {
                        $dependencyIdentifier = $this->getDependencyId($tableName, $entityType, $entityName);
                        $disabledDependencies[$dependencyIdentifier] =
                            $this->resolveEntityDependencies($tableName, $entityType, $entityName);
                    }
                }
            }
            if ($this->isEntityDisabled($tableDeclaration)) {
                $disabledDependencies[$this->getDependencyId($tableName)] =
                    $this->resolveEntityDependencies($tableName, self::SCHEMA_ENTITY_TABLE);
            }
        }

        return $disabledDependencies;
    }

    /**
     * Retrieve dependencies for foreign entities.
     *
     * @param array $constraintDeclaration
     * @return array
     * @throws \Exception
     */
    private function getFKDependencies(array $constraintDeclaration): array
    {
        $referenceDependencyIdentifier =
            $this->getDependencyId(
                $constraintDeclaration['referenceTable'],
                self::SCHEMA_ENTITY_CONSTRAINT,
                $constraintDeclaration['referenceId']
            );
        $dependencyIdentifier =
            $this->getDependencyId(
                $constraintDeclaration[self::SCHEMA_ENTITY_TABLE],
                self::SCHEMA_ENTITY_CONSTRAINT,
                $constraintDeclaration['referenceId']
            );

        $constraintDependencies = [];
        $constraintDependencies[$referenceDependencyIdentifier] =
            $this->resolveEntityDependencies(
                $constraintDeclaration['referenceTable'],
                self::SCHEMA_ENTITY_COLUMN,
                $constraintDeclaration['referenceColumn']
            );
        $constraintDependencies[$dependencyIdentifier] =
            $this->resolveEntityDependencies(
                $constraintDeclaration[self::SCHEMA_ENTITY_TABLE],
                self::SCHEMA_ENTITY_COLUMN,
                $constraintDeclaration[self::SCHEMA_ENTITY_COLUMN]
            );

        return $constraintDependencies;
    }

    /**
     * Retrieve dependencies for constraint entities.
     *
     * @param array $moduleDeclaration
     * @return array
     * @throws \Exception
     */
    private function getConstraintDependencies(array $moduleDeclaration): array
    {
        $constraintDependencies = [];
        foreach ($moduleDeclaration[self::SCHEMA_ENTITY_TABLE] as $tableName => $tableDeclaration) {
            if (empty($tableDeclaration[self::SCHEMA_ENTITY_CONSTRAINT])) {
                continue;
            }
            foreach ($tableDeclaration[self::SCHEMA_ENTITY_CONSTRAINT] as $constraintName => $constraintDeclaration) {
                if ($this->isEntityDisabled($constraintDeclaration)) {
                    continue;
                }
                $dependencyIdentifier =
                    $this->getDependencyId($tableName, self::SCHEMA_ENTITY_CONSTRAINT, $constraintName);
                switch ($constraintDeclaration['type']) {
                    case 'foreign':
                        $constraintDependencies = array_merge(
                            $constraintDependencies,
                            $this->getFKDependencies($constraintDeclaration)
                        );
                        break;
                    case 'primary':
                    case 'unique':
                        $constraintDependencies[$dependencyIdentifier] = $this->getComplexDependency(
                            $tableName,
                            $constraintDeclaration
                        );
                }
            }
        }
        return $constraintDependencies;
    }

    /**
     * Calculate complex dependency.
     *
     * @param string $tableName
     * @param array $entityDeclaration
     * @return array
     * @throws \Exception
     */
    private function getComplexDependency(string $tableName, array $entityDeclaration): array
    {
        $complexDependency = [];
        if (empty($entityDeclaration[self::SCHEMA_ENTITY_COLUMN])) {
            return $complexDependency;
        }

        if (!is_array($entityDeclaration[self::SCHEMA_ENTITY_COLUMN])) {
            $entityDeclaration[self::SCHEMA_ENTITY_COLUMN] = [$entityDeclaration[self::SCHEMA_ENTITY_COLUMN]];
        }

        foreach (array_keys($entityDeclaration[self::SCHEMA_ENTITY_COLUMN]) as $columnName) {
            $complexDependency[] =
                $this->resolveEntityDependencies($tableName, self::SCHEMA_ENTITY_COLUMN, $columnName);
        }

        return array_values($complexDependency);
    }

    /**
     * Retrieve dependencies for index entities.
     *
     * @param array $moduleDeclaration
     * @return array
     * @throws \Exception
     */
    private function getIndexDependencies(array $moduleDeclaration): array
    {
        $indexDependencies = [];
        foreach ($moduleDeclaration[self::SCHEMA_ENTITY_TABLE] as $tableName => $tableDeclaration) {
            if (empty($tableDeclaration[self::SCHEMA_ENTITY_INDEX])) {
                continue;
            }
            foreach ($tableDeclaration[self::SCHEMA_ENTITY_INDEX] as $indexName => $indexDeclaration) {
                if ($this->isEntityDisabled($indexDeclaration)) {
                    continue;
                }
                $dependencyIdentifier =
                    $this->getDependencyId($tableName, self::SCHEMA_ENTITY_INDEX, $indexName);
                $indexDependencies[$dependencyIdentifier] =
                    $this->getComplexDependency($tableName, $indexDeclaration);
            }
        }

        return $indexDependencies;
    }

    /**
     * Check status of the entity declaration.
     *
     * @param array $entityDeclaration
     * @return bool
     */
    private function isEntityDisabled(array $entityDeclaration): bool
    {
        return isset($entityDeclaration['disabled']) && $entityDeclaration['disabled'] == true;
    }

    /**
     * Retrive dependency id.
     *
     * @param string $tableName
     * @param string $entityType
     * @param null|string $entityName
     * @return string
     */
    private function getDependencyId(
        string $tableName,
        string $entityType = self::SCHEMA_ENTITY_TABLE,
        ?string $entityName = null
    ) {
        return implode('___', [$tableName, $entityType, $entityName ?: $tableName]);
    }

    /**
     * Retrieve dependency parameters from dependency id.
     *
     * @param string $id
     * @return array
     */
    private function decodeDependencyId(string $id): array
    {
        $decodedValues = explode('___', $id);
        $result = [
            'tableName' => $decodedValues[0],
            'entityType' => $decodedValues[1],
            'entityName' => $decodedValues[2],
        ];
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
        $decodedId = $this->decodeDependencyId($id);
        $entityType = $decodedId['entityType'];
        if ($entityType === self::SCHEMA_ENTITY_TABLE) {
            $message = sprintf(
                'Table %s has undeclared dependency on one of the next modules.',
                $decodedId['tableName']
            );
        } else {
            $message = sprintf(
                '%s %s from %s table has undeclared dependency on one of the next modules.',
                ucfirst($entityType),
                $decodedId['entityName'],
                $decodedId['tableName']
            );
        }

        return $message;
    }

    /**
     * Collect module dependencies.
     *
     * @param string $currentModuleName
     * @param array $dependencies
     * @return array
     */
    private function collectDependencies($currentModuleName, $dependencies = [])
    {
        if (!count($dependencies)) {
            return [];
        }
        foreach ($dependencies as $dependencyName => $dependency) {
            $this->collectDependency($dependencyName, $dependency, $currentModuleName);
        }

        return $this->getDeclaredDependencies($currentModuleName, self::TYPE_HARD, self::MAP_TYPE_FOUND);
    }

    /**
     * Collect a module dependency.
     *
     * @param string $dependencyName
     * @param array $dependency
     * @param string $currentModule
     */
    private function collectDependency(
        string $dependencyName,
        array $dependency,
        string $currentModule
    ) {
        $declared = $this->getDeclaredDependencies($currentModule, self::TYPE_HARD, self::MAP_TYPE_DECLARED);
        $checkResult = array_intersect($declared, $dependency);

        if (empty($checkResult)) {
            $this->addDependencies(
                $currentModule,
                self::TYPE_HARD,
                self::MAP_TYPE_FOUND,
                [
                    $dependencyName => $dependency,
                ]
            );
        }
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
     * Add dependencies to dependency list.
     *
     * @param string $moduleName
     * @param array $packageNames
     * @param string $type
     *
     * @return void
     * @throws \Exception
     */
    private function presetDependencies(
        string $moduleName,
        array $packageNames,
        string $type
    ): void {
        $packageNames = array_filter($packageNames, function ($packageName) {
            return $this->getModuleName($packageName) ||
                0 === strpos($packageName, 'magento/') && 'magento/magento-composer-installer' != $packageName;
        });

        foreach ($packageNames as $packageName) {
            $this->addDependencies(
                $moduleName,
                $type,
                self::MAP_TYPE_DECLARED,
                [$this->convertModuleName($packageName)]
            );
        }
    }

    /**
     * Converts a composer json component name into the Magento Module form.
     *
     * @param string $jsonName The name of a composer json component or dependency e.g. 'magento/module-theme'
     * @return string The corresponding Magento Module e.g. 'Magento\Theme'
     * @throws \Exception
     */
    private function convertModuleName(string $jsonName): string
    {
        $moduleName = $this->getModuleName($jsonName);
        if ($moduleName) {
            return $moduleName;
        }

        if (
            strpos($jsonName, 'magento/magento') !== false
            || strpos($jsonName, 'magento/framework') !== false
        ) {
            $moduleName = str_replace('/', "\t", $jsonName);
            $moduleName = str_replace('framework-', "Framework\t", $moduleName);
            $moduleName = str_replace('-', ' ', $moduleName);
            $moduleName = ucwords($moduleName);
            $moduleName = str_replace("\t", '\\', $moduleName);
            $moduleName = str_replace(' ', '', $moduleName);
        } else {
            $moduleName = $jsonName;
        }

        return $moduleName;
    }

    /**
     * Returns package name on module name mapping.
     *
     * @return array
     * @throws \Exception
     */
    private function getPackageModuleMapping(): array
    {
        if (!$this->packageModuleMapping) {
            $jsonFiles = Files::init()->getComposerFiles(ComponentRegistrar::MODULE, false);

            $packageModuleMapping = [];
            foreach ($jsonFiles as $file) {
                $moduleXml = simplexml_load_file(dirname($file) . '/etc/module.xml');
                $moduleName = str_replace('_', '\\', (string)$moduleXml->module->attributes()->name);
                $composerJson = $this->readJsonFile($file);
                $packageName = $composerJson->name;
                $packageModuleMapping[$packageName] = $moduleName;
            }

            $this->packageModuleMapping = $packageModuleMapping;
        }

        return $this->packageModuleMapping;
    }

    /**
     * Retrive Magento style module name.
     *
     * @param string $packageName
     * @return null|string
     * @throws \Exception
     */
    private function getModuleName(string $packageName): ?string
    {
        return $this->getPackageModuleMapping()[$packageName] ?? null;
    }

    /**
     * Retrieve array of dependency items.
     *
     * @param $module
     * @param $type
     * @param $mapType
     * @return array
     */
    private function getDeclaredDependencies(string $module, string $type, string $mapType)
    {
        return $this->mapDependencies[$module][$type][$mapType] ?? [];
    }

    /**
     * Add dependency map items.
     *
     * @param $module
     * @param $type
     * @param $mapType
     * @param $dependencies
     */
    protected function addDependencies(string $module, string $type, string $mapType, array $dependencies)
    {
        $this->mapDependencies[$module][$type][$mapType] = array_merge_recursive(
            $this->getDeclaredDependencies($module, $type, $mapType),
            $dependencies
        );
    }
}