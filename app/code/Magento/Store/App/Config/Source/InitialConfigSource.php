<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\App\Config\Source;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\DataObjectFactory;

/**
 * Config source to retrieve configuration from files.
 */
class InitialConfigSource implements ConfigSourceInterface
{
    /**
     * The file reader
     *
     * @var Reader
     */
    private $reader;

    /**
     * The deployment config reader
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * The config type
     *
     * @var string
     */
    private $configType;

    /**
     * The DataObject factory.
     *
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param Reader $reader The file reader
     * @param DeploymentConfig $deploymentConfig The deployment config reader
     * @param DataObjectFactory $dataObjectFactory The DataObject factory
     * @param string $configType The config type
     */
    public function __construct(
        Reader $reader,
        DeploymentConfig $deploymentConfig,
        DataObjectFactory $dataObjectFactory,
        $configType
    ) {
        $this->reader = $reader;
        $this->deploymentConfig = $deploymentConfig;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->configType = $configType;
    }

    /**
     * @inheritdoc
     */
    public function get($path = '')
    {
        /**
         * Magento store configuration should not be read from file source
         * on not installed instance.
         *
         * @see \Magento\Store\Model\Config\Importer To import store configs
         */
        if (!$this->deploymentConfig->isAvailable()) {
            return [];
        }

        $data = $this->dataObjectFactory->create([
            'data' => $this->reader->load()
        ]);

        if ($path !== '' && $path !== null) {
            $path = '/' . ltrim($path, '/');
        }

        return $data->getData($this->configType . $path) ?: [];
    }
}
