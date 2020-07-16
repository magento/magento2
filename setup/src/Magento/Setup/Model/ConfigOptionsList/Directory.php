<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Model\ConfigOptionsList;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Setup\ConfigOptionsListInterface;
use Magento\Framework\Setup\Option\BooleanConfigOption;

/**
 * Deployment configuration options for the document root
 */
class Directory implements ConfigOptionsListInterface
{
    public const INPUT_KEY_DOCUMENT_ROOT_IS_PUB = 'document-root-is-pub';

    /**
     * @inheritdoc
     */
    public function getOptions(): array
    {
        return [
            new BooleanConfigOption(
                self::INPUT_KEY_DOCUMENT_ROOT_IS_PUB,
                ConfigOptionsListConstants::CONFIG_PATH_DOCUMENT_ROOT_IS_PUB,
                "Is the webserver's document root set to ./pub/"
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function createConfig(array $options, DeploymentConfig $deploymentConfig): ConfigData
    {
        $deploymentOption = [
            self::INPUT_KEY_DOCUMENT_ROOT_IS_PUB => ConfigOptionsListConstants::CONFIG_PATH_DOCUMENT_ROOT_IS_PUB,
        ];

        $configData = new ConfigData(ConfigFilePool::APP_ENV);

        foreach ($deploymentOption as $inputKey => $configPath) {
            if (!isset($options[$inputKey])) {
                continue;
            }
            $configData->set($configPath, (int)$this->boolVal($options[$inputKey]));
        }

        return $configData;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $options, DeploymentConfig $deploymentConfig): array
    {
        return [];
    }

    /**
     * Convert any valid input option to a boolean
     *
     * @param mixed $option
     *
     * @return bool
     */
    private function boolVal($option): bool
    {
        return in_array(strtolower((string)$option), BooleanConfigOption::OPTIONS_POSITIVE);
    }
}
