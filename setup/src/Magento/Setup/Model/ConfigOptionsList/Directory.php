<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Model\ConfigOptionsList;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Setup\ConfigOptionsListInterface;
use Magento\Framework\Setup\Option\SelectConfigOption;

/**
 * Deployment configuration options for the folders.
 */
class Directory implements ConfigOptionsListInterface
{
    /**
     * Input key for config command.
     */
    private const INPUT_KEY_DOCUMENT_ROOT_IS_PUB = 'document-root-is-pub';

    /**
     * Path in in configuration.
     */
    const CONFIG_PATH_DOCUMENT_ROOT_IS_PUB = 'directories/document_root_is_pub';

    /**
     * The available configuration values.
     *
     * @var array
     */
    private $selectOptions = [true, false];

    /**
     * Create config and update document root value according to provided options
     *
     * @param array $options
     * @param DeploymentConfig $deploymentConfig
     * @return ConfigData|ConfigData[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function createConfig(array $options, DeploymentConfig $deploymentConfig)
    {
        $configData = new ConfigData(ConfigFilePool::APP_ENV);
        if (isset($options[self::INPUT_KEY_DOCUMENT_ROOT_IS_PUB])) {
            $configData->set(
                self::CONFIG_PATH_DOCUMENT_ROOT_IS_PUB,
                \filter_var($options[self::INPUT_KEY_DOCUMENT_ROOT_IS_PUB], FILTER_VALIDATE_BOOLEAN)
            );
        }

        return $configData;
    }

    /**
     * Return options from Directory configuration.
     *
     * @return \Magento\Framework\Setup\Option\AbstractConfigOption[]|SelectConfigOption[]
     */
    public function getOptions()
    {
        return [
            new SelectConfigOption(
                self::INPUT_KEY_DOCUMENT_ROOT_IS_PUB,
                SelectConfigOption::FRONTEND_WIZARD_SELECT,
                $this->selectOptions,
                self::CONFIG_PATH_DOCUMENT_ROOT_IS_PUB,
                'Flag to show is Pub is on root, can be true or false only',
                false
            ),
        ];
    }

    /**
     * Validate options.
     *
     * @param array $options
     * @param DeploymentConfig $deploymentConfig
     * @return array|string[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate(array $options, DeploymentConfig $deploymentConfig)
    {
        return [];
    }
}
