<?php

namespace Magento\Test\Integrity\Dependency;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;

abstract class DependencyProvider
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
     * @var array
     */
    protected $mapDependencies = [];

    /**
     * @var array
     */
    protected $packageModuleMapping = [];

    /**
     * Retrieve array of dependency items.
     *
     * @param $module
     * @param $type
     * @param $mapType
     * @return array
     */
    abstract protected function getDeclaredDependencies(string $module, string $type, string $mapType);

    /**
     * @param string $moduleName
     * @return array
     */
    abstract public function getDeclaredExistingModuleDependencies(string $moduleName): array;

    /**
     * @param string $moduleName
     * @return array
     */
    abstract public function getUndeclaredModuleDependencies(string $moduleName): array;

    /**
     * Initialise map of dependencies.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function initDeclaredDependencies()
    {
        if (empty($this->mapDependencies)) {
            $jsonFiles = Files::init()->getComposerFiles(ComponentRegistrar::MODULE, false);
            foreach ($jsonFiles as $file) {
                $json = new \Magento\Framework\Config\Composer\Package($this->readJsonFile($file));
                $moduleName = $this->convertModuleName($json->get('name'));
                $require = array_keys((array)$json->get('require'));
                $this->presetDependencies($moduleName, $require, self::TYPE_HARD);
            }
        }
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
    protected function presetDependencies(
        string $moduleName,
        array $packageNames,
        string $type
    ): void
    {
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
     * @param string $jsonName
     * @return string
     * @throws \Exception
     */
    protected function convertModuleName(string $jsonName): string
    {
        $moduleName = $this->getModuleName($jsonName);
        if ($moduleName) {
            return $moduleName;
        }

        if (strpos($jsonName, 'magento/magento') !== false
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
     * Read data from json file.
     *
     * @param string $file
     * @return mixed
     * @throws \Exception
     */
    protected function readJsonFile(string $file, bool $asArray = false)
    {
        $decodedJson = json_decode(file_get_contents($file), $asArray);
        if (null == $decodedJson) {
            //phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception("Invalid Json: $file");
        }

        return $decodedJson;
    }

    /**
     * Retrieve Magento style module name.
     *
     * @param string $packageName
     * @return null|string
     * @throws \Exception
     */
    protected function getModuleName(string $packageName): ?string
    {
        return $this->getPackageModuleMapping()[$packageName] ?? null;
    }

    /**
     * Returns package name on module name mapping.
     *
     * @return array
     * @throws \Exception
     */
    protected function getPackageModuleMapping(): array
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
