<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command\ConfigSet;

use Magento\Config\App\Config\Type\System;
use Magento\Config\Console\Command\ConfigSetCommand;
use Magento\Config\Model\Config\Factory as ConfigFactory;
use Magento\Framework\App\Config\ConfigPathResolver;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Config\Model\PreparedValueFactory;

/**
 * Processes default flow of config:set command.
 *
 * This processor saves the value of configuration into database.
 *
 * @inheritdoc
 * @api
 * @since 101.0.0
 */
class DefaultProcessor implements ConfigSetProcessorInterface
{
    /**
     * The deployment configuration reader.
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * The resolver for configuration paths according to source type.
     *
     * @var ConfigPathResolver
     */
    private $configPathResolver;

    /**
     * The factory for prepared value.
     *
     * @var PreparedValueFactory
     */
    private $preparedValueFactory;

    /**
     * @var ConfigFactory
     */
    private $configFactory;

    /**
     * @param PreparedValueFactory $preparedValueFactory The factory for prepared value
     * @param DeploymentConfig $deploymentConfig The deployment configuration reader
     * @param ConfigPathResolver $configPathResolver The resolver for configuration paths according to source type
     * @param ConfigFactory|null $configFactory
     */
    public function __construct(
        PreparedValueFactory $preparedValueFactory,
        DeploymentConfig $deploymentConfig,
        ConfigPathResolver $configPathResolver,
        ConfigFactory $configFactory = null
    ) {
        $this->preparedValueFactory = $preparedValueFactory;
        $this->deploymentConfig = $deploymentConfig;
        $this->configPathResolver = $configPathResolver;

        $this->configFactory = $configFactory ?? ObjectManager::getInstance()->get(ConfigFactory::class);
    }

    /**
     * Processes database flow of config:set command.
     *
     * Requires installed application.
     *
     * @inheritdoc
     * @since 101.0.0
     */
    public function process($path, $value, $scope, $scopeCode)
    {
        if ($this->isLocked($path, $scope, $scopeCode)) {
            throw new CouldNotSaveException(
                __(
                    'The value you set has already been locked. To change the value, use the --%1 option.',
                    ConfigSetCommand::OPTION_LOCK_ENV
                )
            );
        }

        try {
            $config = $this->configFactory->create(['data' => [
                'scope' => $scope,
                'scope_code' => $scopeCode,
            ]]);
            $config->setDataByPath($path, $value);
            $config->save();
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('%1', $exception->getMessage()), $exception);
        }
    }

    /**
     * Checks whether configuration is locked in file storage.
     *
     * @param string $path The path to configuration
     * @param string $scope The scope of configuration
     * @param string $scopeCode The scope code of configuration
     * @return bool
     */
    private function isLocked($path, $scope, $scopeCode)
    {
        $scopePath = $this->configPathResolver->resolve($path, $scope, $scopeCode, System::CONFIG_TYPE);

        return $this->deploymentConfig->get($scopePath) !== null;
    }
}
