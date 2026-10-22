<?php
/**
 * Configuration metadata processor
 *
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config;

use Magento\Framework\App\Config\Data\EnvironmentValueProcessorInterface;
use Magento\Framework\App\Config\Data\ProcessorFactory;
use Magento\Framework\App\Config\Spi\PostProcessorInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Post-process config values using their backend models.
 */
class MetadataConfigTypeProcessor implements PostProcessorInterface
{
    /**
     * @var ProcessorFactory
     */
    protected $_processorFactory;

    /**
     * @var array
     */
    protected $_metadata = [];

    /**
     * Source of configurations
     *
     * @var ConfigSourceInterface
     */
    private $configSource;

    /**
     * The resolver for configuration paths
     *
     * @var ConfigPathResolver
     */
    private $configPathResolver;

    /**
     * Environment values indexed by metadata path, scope type and code for the current process call.
     *
     * @var array|null
     */
    private $environmentValues;

    /**
     * @param ProcessorFactory $processorFactory
     * @param Initial $initialConfig
     * @param ConfigSourceInterface $configSource Source of configurations
     * @param ConfigPathResolver $configPathResolver The resolver for configuration paths
     */
    public function __construct(
        ProcessorFactory $processorFactory,
        Initial $initialConfig,
        ?ConfigSourceInterface $configSource = null,
        ?ConfigPathResolver $configPathResolver = null
    ) {
        $this->_processorFactory = $processorFactory;
        $this->_metadata = $initialConfig->getMetadata();
        $this->configSource = $configSource
            ?: ObjectManager::getInstance()->get(ConfigSourceInterface::class);
        $this->configPathResolver = $configPathResolver
            ?: ObjectManager::getInstance()->get(ConfigPathResolver::class);
    }

    /**
     * Retrieve array value by path
     *
     * @param array $data
     * @param string $path
     * @return string|null
     */
    protected function _getValue(array $data, $path)
    {
        $keys = explode('/', $path);
        foreach ($keys as $key) {
            if (is_array($data) && array_key_exists($key, $data)) {
                $data = $data[$key];
            } else {
                return null;
            }
        }
        return $data;
    }

    /**
     * Set array value by path
     *
     * @param array &$container
     * @param string $path
     * @param string $value
     * @return void
     */
    protected function _setValue(array &$container, $path, $value)
    {
        $segments = explode('/', $path);
        $currentPointer = & $container;
        foreach ($segments as $segment) {
            if (!isset($currentPointer[$segment])) {
                $currentPointer[$segment] = [];
            }
            $currentPointer = & $currentPointer[$segment];
        }
        $currentPointer = $value;
    }

    /**
     * Process data by sections: stores, default, websites and by scope codes.
     *
     * Uses environment processing when supported for values provided through $_ENV variables.
     *
     * @param array $data An array of scope configuration
     * @param string $scope The configuration scope
     * @param string|null $scopeCode The configuration scope code
     * @return array An array of processed configuration
     */
    private function processScopeData(
        array $data,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        foreach ($this->_metadata as $path => $metadata) {
            try {
                if ($this->environmentValues === null) {
                    $this->environmentValues = $this->getEnvironmentValues($this->configSource->get());
                }
                $configPath = $this->configPathResolver->resolve($path, $scope, $scopeCode);
                $isEnvironmentValue = !empty($this->configSource->get($configPath));
            } catch (\Throwable $exception) {
                //Failed to load scopes or config source, perhaps config data received is outdated.
                return $data;
            }

            if (isset($metadata['backendModel'])) {
                /** @var \Magento\Framework\App\Config\Data\ProcessorInterface $processor */
                $processor = $this->_processorFactory->get($metadata['backendModel']);
                $value = $this->_getValue($data, $path);
                $isEnvironmentValue = $isEnvironmentValue || (is_scalar($value)
                    && in_array((string)$value, $this->getEnvironmentCandidates($path, $scope, $scopeCode), true));
                if ($isEnvironmentValue) {
                    if (!$processor instanceof EnvironmentValueProcessorInterface) {
                        continue;
                    }
                    $value = $processor->processEnvironmentValue($value);
                } else {
                    $value = $processor->processValue($value);
                }
                $this->_setValue($data, $path, $value);
            }
        }

        return $data;
    }

    /**
     * Environment values the current scope may have inherited the value of the given path from.
     *
     * @param string $path
     * @param string $scope
     * @param string|null $scopeCode
     * @return string[]
     */
    private function getEnvironmentCandidates(string $path, string $scope, $scopeCode): array
    {
        $environmentValues = $this->environmentValues[$path] ?? [];
        $candidates = array_values($environmentValues[ScopeConfigInterface::SCOPE_TYPE_DEFAULT] ?? []);
        if ($scope !== ScopeConfigInterface::SCOPE_TYPE_DEFAULT && isset($environmentValues[$scope][$scopeCode])) {
            $candidates[] = $environmentValues[$scope][$scopeCode];
        }
        if ($scope === 'stores') {
            foreach ($environmentValues['websites'] ?? [] as $websiteValue) {
                $candidates[] = $websiteValue;
            }
        }

        return $candidates;
    }

    /**
     * Collect scalar environment values for each backend metadata path, grouped by scope type and code.
     *
     * @param array $environmentData
     * @return array
     */
    private function getEnvironmentValues(array $environmentData): array
    {
        $scopeData = [
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT => [
                '' => $environmentData[ScopeConfigInterface::SCOPE_TYPE_DEFAULT] ?? []
            ],
            'websites' => $environmentData['websites'] ?? [],
            'stores' => $environmentData['stores'] ?? []
        ];
        $values = [];
        foreach ($this->_metadata as $path => $metadata) {
            if (!isset($metadata['backendModel'])) {
                continue;
            }
            foreach ($scopeData as $scope => $scopes) {
                foreach ($scopes as $scopeCode => $data) {
                    $value = $this->_getValue($data, $path);
                    if (is_scalar($value)) {
                        $values[$path][$scope][$scopeCode] = (string)$value;
                    }
                }
            }
        }
        return $values;
    }

    /**
     * Process config data
     *
     * @param array $rawData An array of configuration
     * @return array
     */
    public function process(array $rawData)
    {
        $this->environmentValues = null;
        $processedData = [];
        foreach ($rawData as $scope => $scopeData) {
            if ($scope == ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
                $processedData[ScopeConfigInterface::SCOPE_TYPE_DEFAULT] = $this->processScopeData($scopeData);
            } else {
                foreach ($scopeData as $scopeCode => $data) {
                    $processedData[$scope][$scopeCode] = $this->processScopeData($data, $scope, $scopeCode);
                }
            }
        }

        return $processedData;
    }
}
