<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Test\Integrity\Dependency;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;

class DependencyProvider
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
    private $mapDependencies = [];

    /**
     * @var array
     */
    private $packageModuleMapping = [];

    /**
     * Initialise map of dependencies.
     *
     * @throws \Magento\TestFramework\Inspection\Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initDeclaredDependencies()
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
     * Add dependency map items.
     *
     * @param $module
     * @param $type
     * @param $mapType
     * @param $dependencies
     */
    public function addDependencies(string $module, string $type, string $mapType, array $dependencies)
    {
        $this->mapDependencies[$module][$type][$mapType] = array_merge_recursive(
            $this->getDeclaredDependencies($module, $type, $mapType),
            $dependencies
        );
    }

    /**
     * Retrieve array of dependency items.
     *
     * @param $module
     * @param $type
     * @param $mapType
     * @return array
     */
    public function getDeclaredDependencies(string $module, string $type, string $mapType): array
    {
        return $this->mapDependencies[$module][$type][$mapType] ?? [];
    }

    /**
     * Add dependencies to dependency list.
     *
     * @param string $moduleName
     * @param array $packageNames
     * @param string $type
     *
     * @return void
     * @throws \Magento\TestFramework\Inspection\Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function presetDependencies(string $moduleName, array $packageNames, string $type): void
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
     * @throws \Magento\TestFramework\Inspection\Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function convertModuleName(string $jsonName): string
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
     * @throws \Magento\TestFramework\Inspection\Exception
     */
    private function readJsonFile(string $file, bool $asArray = false)
    {
        $decodedJson = json_decode(file_get_contents($file), $asArray);
        if (null == $decodedJson) {
            throw new \Magento\TestFramework\Inspection\Exception("Invalid Json: $file");
        }

        return $decodedJson;
    }

    /**
     * Retrieve Magento style module name.
     *
     * @param string $packageName
     * @return null|string
     * @throws \Magento\TestFramework\Inspection\Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getModuleName(string $packageName): ?string
    {
        return $this->getPackageModuleMapping()[$packageName] ?? null;
    }

    /**
     * Returns package name on module name mapping.
     *
     * @return array
     * @throws \Magento\TestFramework\Inspection\Exception
     * @throws \Magento\Framework\Exception\LocalizedException
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
}
