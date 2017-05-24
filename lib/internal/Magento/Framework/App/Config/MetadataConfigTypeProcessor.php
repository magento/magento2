<?php
/**
 * Configuration metadata processor
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

use Magento\Config\App\Config\Source\EnvironmentConfigSource;
use Magento\Framework\App\Config\Data\ProcessorFactory;
use Magento\Framework\App\Config\Spi\PostProcessorInterface;
use Magento\Framework\App\ObjectManager;

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
     * The environment configuration
     *
     * @var EnvironmentConfigSource
     */
    private $environmentConfigSource;

    /**
     * The resolver for configuration paths
     *
     * @var ConfigPathResolver
     */
    private $configPathResolver;

    /**
     * @param ProcessorFactory $processorFactory
     * @param Initial $initialConfig
     * @param EnvironmentConfigSource $environmentConfigSource The environment configuration
     * @param ConfigPathResolver $configPathResolver The resolver for configuration paths
     */
    public function __construct(
        ProcessorFactory $processorFactory,
        Initial $initialConfig,
        EnvironmentConfigSource $environmentConfigSource = null,
        ConfigPathResolver $configPathResolver = null
    ) {
        $this->_processorFactory = $processorFactory;
        $this->_metadata = $initialConfig->getMetadata();
        $this->environmentConfigSource = $environmentConfigSource
            ?: ObjectManager::getInstance()->get(EnvironmentConfigSource::class);
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
     * Doesn't processes configuration values that present in $_ENV variables.
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
            $configPath = $this->configPathResolver->resolve($path, $scope, $scopeCode);
            if (!empty($this->environmentConfigSource->get($configPath))) {
                continue;
            }
            /** @var \Magento\Framework\App\Config\Data\ProcessorInterface $processor */
            $processor = $this->_processorFactory->get($metadata['backendModel']);
            $value = $processor->processValue($this->_getValue($data, $path));
            $this->_setValue($data, $path, $value);
        }

        return $data;
    }

    /**
     * Process config data
     *
     * @param array $rawData An array of configuration
     * @return array
     */
    public function process(array $rawData)
    {
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
