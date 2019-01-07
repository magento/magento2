<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\Config\ConfigOptionsListConstants as SetupConfigOptionsList;
use Magento\Backend\Setup\ConfigOptionsList as BackendConfigOptionsList;
use Magento\Setup\Model\StoreConfigurationDataMapper as UserConfig;
use Magento\Setup\Console\Command\InstallCommand;

/**
 * Converter of request data into format compatible with models.
 */
class RequestDataConverter
{
    /**
     * Convert request data into format compatible with models.
     *
     * @param array $source
     * @return array
     */
    public function convert(array $source)
    {
        $result = array_merge(
            $this->convertDeploymentConfigForm($source),
            $this->convertUserConfigForm($source),
            $this->convertAdminUserForm($source)
        );
        return $result;
    }

    /**
     * Convert data from request to format of deployment config model
     *
     * @param array $source
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function convertDeploymentConfigForm(array $source)
    {
        $result = [];
        $result[SetupConfigOptionsList::INPUT_KEY_DB_HOST] = $source['db']['host'] ?? '';
        $result[SetupConfigOptionsList::INPUT_KEY_DB_NAME] = $source['db']['name'] ?? '';
        $result[SetupConfigOptionsList::INPUT_KEY_DB_USER] = $source['db']['user'] ?? '';
        $result[SetupConfigOptionsList::INPUT_KEY_DB_PASSWORD] =
            $source['db']['password'] ?? '';
        $result[SetupConfigOptionsList::INPUT_KEY_DB_PREFIX] =
            $source['db']['tablePrefix'] ?? '';
        $result[BackendConfigOptionsList::INPUT_KEY_BACKEND_FRONTNAME] = $source['config']['address']['admin'] ?? '';
        $result[SetupConfigOptionsList::INPUT_KEY_ENCRYPTION_KEY] = $source['config']['encrypt']['key'] ?? null;
        $result[SetupConfigOptionsList::INPUT_KEY_SESSION_SAVE] = $source['config']['sessionSave']['type'] ?? SetupConfigOptionsList::SESSION_SAVE_FILES;
        $result[Installer::ENABLE_MODULES] = isset($source['store']['selectedModules'])
            ? implode(',', $source['store']['selectedModules']) : '';
        $result[Installer::DISABLE_MODULES] = isset($source['store']['allModules'])
            ? implode(',', array_diff($source['store']['allModules'], $source['store']['selectedModules'])) : '';
        return $result;
    }

    /**
     * Convert data from request to format of user config model
     *
     * @param array $source
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function convertUserConfigForm(array $source)
    {
        $result = [];
        if (isset($source['config']['address']['base_url']) && !empty($source['config']['address']['base_url'])) {
            $result[UserConfig::KEY_BASE_URL] = $source['config']['address']['base_url'];
        }
        $result[UserConfig::KEY_USE_SEF_URL] = $source['config']['rewrites']['allowed'] ?? '';
        $result[UserConfig::KEY_IS_SECURE] = $source['config']['https']['front'] ?? '';
        $result[UserConfig::KEY_IS_SECURE_ADMIN] = $source['config']['https']['admin'] ?? '';
        $result[UserConfig::KEY_BASE_URL_SECURE] = (isset($source['config']['https']['front'])
            || isset($source['config']['https']['admin']))
            ? $source['config']['https']['text'] : '';
        $result[UserConfig::KEY_LANGUAGE] = $source['store']['language'] ?? '';
        $result[UserConfig::KEY_TIMEZONE] = $source['store']['timezone'] ?? '';
        $result[UserConfig::KEY_CURRENCY] = $source['store']['currency'] ?? '';
        $result[InstallCommand::INPUT_KEY_USE_SAMPLE_DATA] = $source['store']['useSampleData'] ?? '';
        $result[InstallCommand::INPUT_KEY_CLEANUP_DB] = $source['store']['cleanUpDatabase'] ?? '';
        return $result;
    }

    /**
     * Convert data from request to format of admin account model
     *
     * @param array $source
     * @return array
     */
    private function convertAdminUserForm(array $source)
    {
        $result = [];
        $result[AdminAccount::KEY_USER] = $source['admin']['username'] ?? '';
        $result[AdminAccount::KEY_PASSWORD] = $source['admin']['password'] ?? '';
        $result[AdminAccount::KEY_EMAIL] = $source['admin']['email'] ?? '';
        $result[AdminAccount::KEY_FIRST_NAME] = $result[AdminAccount::KEY_USER];
        $result[AdminAccount::KEY_LAST_NAME] = $result[AdminAccount::KEY_USER];
        return $result;
    }
}
