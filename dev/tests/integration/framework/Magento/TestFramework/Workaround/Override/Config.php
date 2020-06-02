<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Workaround\Override;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\ConverterInterface;
use Magento\Framework\Config\Reader\Filesystem;
use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Framework\View\File\Collector\Decorator\ModuleDependency;
use Magento\Framework\View\File\Collector\Decorator\ModuleOutput;
use Magento\Framework\View\File\CollectorInterface;
use Magento\TestFramework\Workaround\Override\Config\Converter;
use Magento\TestFramework\Workaround\Override\Config\Dom;
use Magento\TestFramework\Workaround\Override\Config\FileCollector;
use Magento\TestFramework\Workaround\Override\Config\FileResolver;
use Magento\TestFramework\Workaround\Override\Config\SchemaLocator;
use Magento\TestFramework\Workaround\Override\Config\ValidationState;
use PHPUnit\Framework\TestCase;

/**
 * Provides integration tests configuration.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Config implements ConfigInterface
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
     * Self instance getter.
     *
     * @return ConfigInterface
     */
    public static function getInstance(): ConfigInterface
    {
        if (empty(self::$instance)) {
            throw new \RuntimeException('Override config isn\'t initialized');
        }

        return self::$instance;
    }

    /**
     * Self instance setter.
     *
     * @param ConfigInterface $config
     * @return void
     */
    public static function setInstance(ConfigInterface $config): void
    {
        self::$instance = $config;
    }

    /**
     * Reads configuration from files.
     *
     * @return void
     */
    public function init(): void
    {
        if (empty($this->config)) {
            $data = [];
            $useConfig = (defined('USE_OVERRIDE_CONFIG') && USE_OVERRIDE_CONFIG === 'enabled');

            if ($useConfig) {
                $reader = ObjectManager::getInstance()->create(
                    Filesystem::class,
                    [
                        'fileName' => 'overrides.xml',
                        'fileResolver' => $this->getFileResolver(),
                        'idAttributes' => [
                            '/overrides/test' => 'class',
                            '/overrides/test/method' => 'name',
                            '/overrides/test/method/dataSet' => 'name',
                        ],
                        'schemaLocator' => $this->getSchemaLocator(),
                        'validationState' => $this->getValidationState(),
                        'converter' => $this->getConverter(),
                        'domDocumentClass' => $this->getDomClass(),
                    ]
                );
                $data = $reader->read();
            }

            $this->config = $data;
        }
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function hasSkippedTest(string $className): bool
    {
        $classConfig = $this->config[$className] ?? [];

        return $this->isSkippedByConfig($classConfig);
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
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
     * Returns file resolver.
     *
     * @return FileResolver
     */
    protected function getFileResolver(): FileResolver
    {
        return ObjectManager::getInstance()->create(
            FileResolver::class,
            [
                'baseFiles' => ObjectManager::getInstance()->create(
                    ModuleDependency::class,
                    [
                        'subject' => ObjectManager::getInstance()->create(
                            ModuleOutput::class,
                            [
                                'subject' => $this->getFileCollector()
                            ]
                        )
                    ]
                )
            ]
        );
    }

    /**
     * Returns schema locator.
     *
     * @return SchemaLocatorInterface
     */
    protected function getSchemaLocator(): SchemaLocatorInterface
    {
        return ObjectManager::getInstance()->create(SchemaLocator::class);
    }

    /**
     * Returns validation state.
     *
     * @return ValidationStateInterface
     */
    protected function getValidationState(): ValidationStateInterface
    {
        return ObjectManager::getInstance()->create(ValidationState::class);
    }

    /**
     * Returns converter for config files.
     *
     * @return ConverterInterface
     */
    protected function getConverter(): ConverterInterface
    {
        return ObjectManager::getInstance()->create(Converter::class);
    }

    /**
     * Returns DOM class name.
     *
     * @return string
     */
    protected function getDomClass(): string
    {
        return Dom::class;
    }

    /**
     * Returns file collector.
     *
     * @return CollectorInterface
     */
    protected function getFileCollector(): CollectorInterface
    {
        return ObjectManager::getInstance()->create(FileCollector::class);
    }

    /**
     * Check that class has even one test skipped
     *
     * @param array $config
     * @return bool
     */
    private function isSkippedByConfig(array $config): bool
    {
        $result = false;
        if (isset($config['skip']) && $config['skip']) {
            $result = true;
        } else {
            foreach ($config as $lowerLevelConfig) {
                if (is_array($lowerLevelConfig)) {
                    $result = $this->isSkippedByConfig($lowerLevelConfig);
                    if ($result === true) {
                        break;
                    }
                }
            }
        }

        return $result;
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
