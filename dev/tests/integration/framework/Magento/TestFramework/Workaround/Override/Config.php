<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Workaround\Override;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\Reader\Filesystem;
use Magento\Framework\View\File\Collector\Decorator\ModuleDependency;
use Magento\Framework\View\File\Collector\Decorator\ModuleOutput;
use Magento\TestFramework\Workaround\Override\Config\Converter;
use Magento\TestFramework\Workaround\Override\Config\FileCollector;
use Magento\TestFramework\Workaround\Override\Config\FileResolver;
use Magento\TestFramework\Workaround\Override\Config\Dom;
use Magento\TestFramework\Workaround\Override\Config\SchemaLocator;
use Magento\TestFramework\Workaround\Override\Config\ValidationState;
use PHPUnit\Framework\TestCase;

/**
 * Provides integration tests configuration.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Config
{
    /**
     * @var self
     */
    private static $instance;

    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Returns an array with skip key and skipMessage key if test is skipped.
     *
     * @param TestCase $test
     * @return array
     */
    public function getSkipConfiguration(TestCase $test): array
    {
        $classConfig = $this->getClassConfig($test);
        $testConfig = $this->getMethodConfig($test);
        $dataSetConfig = $this->getDataSetConfig($test);
        $result['skip'] = false;

        if (isset($dataSetConfig['skip']) && $dataSetConfig['skip']) {
            $result = $this->prepareSkipConfig($dataSetConfig);
        } elseif (isset($testConfig['skip']) && $testConfig['skip']) {
            $result = $this->prepareSkipConfig($testConfig);
        } elseif (isset($classConfig['skip']) && $classConfig['skip']) {
            $result = $this->prepareSkipConfig($classConfig);
        }

        return $result;
    }

    /**
     * Test has configuration flag.
     *
     * @param string $className
     * @return bool
     */
    public function hasSkippedTest(string $className): bool
    {
        $classConfig = $this->config[$className] ?? [];

        return $this->isSkippedByConfig($classConfig);
    }

    /**
     * Check that class has even one test skipped
     *
     * @param array $config
     * @return bool
     */
    private function isSkippedByConfig(array $config): bool
    {
        if (isset($config['skip']) && $config['skip']) {
            return true;
        }

        foreach ($config as $lowerLevelConfig) {
            if (is_array($lowerLevelConfig)) {
                return $this->isSkippedByConfig($lowerLevelConfig);
            }
        }

        return false;
    }

    /**
     * Self instance getter.
     *
     * @return static
     */
    public static function getInstance(): self
    {
        if (empty(self::$instance)) {
            $data = [];
            $objectManager = ObjectManager::getInstance();
            $useConfig = (defined('USE_OVERRIDE_CONFIG') && USE_OVERRIDE_CONFIG === 'enabled');

            if ($useConfig) {
                $fileResolver = $objectManager->create(
                    FileResolver::class,
                    [
                        'baseFiles' => $objectManager->create(
                            ModuleDependency::class,
                            [
                                'subject' => $objectManager->create(
                                    ModuleOutput::class,
                                    [
                                        'subject' => $objectManager->create(FileCollector::class)
                                    ]
                                )
                            ]
                        )
                    ]
                );
                $reader = $objectManager->create(
                    Filesystem::class,
                    [
                        'fileName' => 'overrides.xml',
                        'fileResolver' => $fileResolver,
                        'idAttributes' => [
                            '/overrides/test' => 'class',
                            '/overrides/test/method' => 'name',
                            '/overrides/test/method/dataSet' => 'name',
                        ],
                        'schemaLocator' => $objectManager->create(SchemaLocator::class),
                        'validationState' => $objectManager->create(ValidationState::class),
                        'converter' => $objectManager->create(Converter::class),
                        'domDocumentClass' => Dom::class,
                    ]
                );
                $data = $reader->read();
            }

            self::$instance = new self($data);
        }

        return self::$instance;
    }

    /**
     * Get config from class node
     *
     * @param TestCase $test
     * @param string|null $fixtureType
     * @return array
     */
    public function getClassConfig(TestCase $test, ?string $fixtureType = null): array
    {
        $result = $this->config[$this->getOriginalClassName($test)] ?? [];
        if ($fixtureType) {
            $result = $result[$fixtureType] ?? [];
        }

        return $result;
    }

    /**
     * Get config from method node
     *
     * @param TestCase $test
     * @param string|null $fixtureType
     * @return array
     */
    public function getMethodConfig(TestCase $test, ?string $fixtureType = null): array
    {
        $config = $this->getClassConfig($test)[$test->getName(false)] ?? [];
        if ($fixtureType) {
            $config = $config[$fixtureType] ?? [];
        }

        return $config;
    }

    /**
     * Get config from dataSet node
     *
     * @param TestCase $test
     * @param string|null $fixtureType
     * @return array
     */
    public function getDataSetConfig(TestCase $test, ?string $fixtureType = null): array
    {
        $config = $this->getClassConfig($test)[$test->getName(false)][(string)$test->dataName()] ?? [];
        if ($fixtureType) {
            $config = $config[$fixtureType] ?? [];
        }

        return $config;
    }

    /**
     * Returns original test class name.
     *
     * @param TestCase $test
     * @return string
     */
    private function getOriginalClassName(TestCase $test)
    {
        return str_replace('\\' . WrapperGenerator::SKIPPABLE_SUFFIX, '', get_class($test));
    }

    /**
     * Get skipped message
     *
     * @param array $config
     * @return array
     */
    private function prepareSkipConfig(array $config): array
    {
        return [
            'skip' => $config['skip'],
            'skipMessage' => $config['skipMessage'] ?: 'Skipped according to override configurations',
        ];
    }
}
