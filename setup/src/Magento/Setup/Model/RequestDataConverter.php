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
        $result[SetupConfigOptionsList::INPUT_KEY_DB_HOST] = isset($source['db']['host']) ? $source['db']['host'] : '';
        $result[SetupConfigOptionsList::INPUT_KEY_DB_NAME] = isset($source['db']['name']) ? $source['db']['name'] : '';
        $result[SetupConfigOptionsList::INPUT_KEY_DB_USER] = isset($source['db']['user']) ? $source['db']['user'] : '';
        $result[SetupConfigOptionsList::INPUT_KEY_DB_PASSWORD] =
            isset($source['db']['password']) ? $source['db']['password'] : '';
        $result[SetupConfigOptionsList::INPUT_KEY_DB_PREFIX] =
            isset($source['db']['tablePrefix']) ? $source['db']['tablePrefix'] : '';
        $result[BackendConfigOptionsList::INPUT_KEY_BACKEND_FRONTNAME] = isset($source['config']['address']['admin'])
            ? $source['config']['address']['admin'] : '';
        $result[SetupConfigOptionsList::INPUT_KEY_DB_SSL_KEY] = isset($source['db']['driverOptionsSslKey'])
            ? $source['db']['driverOptionsSslKey'] : '';
        $result[SetupConfigOptionsList::INPUT_KEY_DB_SSL_CERT] = isset($source['db']['driverOptionsSslCert'])
            ? $source['db']['driverOptionsSslCert'] : '';
        $result[SetupConfigOptionsList::INPUT_KEY_DB_SSL_CA] = isset($source['db']['driverOptionsSslCa'])
            ? $source['db']['driverOptionsSslCa'] : '';
        $result[SetupConfigOptionsList::INPUT_KEY_DB_SSL_VERIFY] = isset($source['db']['driverOptionsSslVerify'])
            ? $source['db']['driverOptionsSslVerify'] : '';
        $result[SetupConfigOptionsList::INPUT_KEY_ENCRYPTION_KEY] = isset($source['config']['encrypt']['key'])
            ? $source['config']['encrypt']['key'] : null;
        $result[SetupConfigOptionsList::INPUT_KEY_SESSION_SAVE] = isset($source['config']['sessionSave']['type'])
            ? $source['config']['sessionSave']['type'] : SetupConfigOptionsList::SESSION_SAVE_FILES;
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
        $result[UserConfig::KEY_USE_SEF_URL] = isset($source['config']['rewrites']['allowed'])
            ? $source['config']['rewrites']['allowed'] : '';
        $result[UserConfig::KEY_IS_SECURE] = isset($source['config']['https']['front'])
            ? $source['config']['https']['front'] : '';
        $result[UserConfig::KEY_IS_SECURE_ADMIN] = isset($source['config']['https']['admin'])
            ? $source['config']['https']['admin'] : '';
        $result[UserConfig::KEY_BASE_URL_SECURE] = (isset($source['config']['https']['front'])
            || isset($source['config']['https']['admin']))
            ? $source['config']['https']['text'] : '';
        $result[UserConfig::KEY_LANGUAGE] = isset($source['store']['language'])
            ? $source['store']['language'] : '';
        $result[UserConfig::KEY_TIMEZONE] = isset($source['store']['timezone'])
            ? $source['store']['timezone'] : '';
        $result[UserConfig::KEY_CURRENCY] = isset($source['store']['currency'])
            ? $source['store']['currency'] : '';
        $result[InstallCommand::INPUT_KEY_USE_SAMPLE_DATA] = isset($source['store']['useSampleData'])
            ? $source['store']['useSampleData'] : '';
        $result[InstallCommand::INPUT_KEY_CLEANUP_DB] = isset($source['store']['cleanUpDatabase'])
            ? $source['store']['cleanUpDatabase'] : '';
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
        $result[AdminAccount::KEY_USER] = isset($source['admin']['username']) ? $source['admin']['username'] : '';
        $result[AdminAccount::KEY_PASSWORD] = isset($source['admin']['password']) ? $source['admin']['password'] : '';
        $result[AdminAccount::KEY_EMAIL] = isset($source['admin']['email']) ? $source['admin']['email'] : '';
        $result[AdminAccount::KEY_FIRST_NAME] = $result[AdminAccount::KEY_USER];
        $result[AdminAccount::KEY_LAST_NAME] = $result[AdminAccount::KEY_USER];
        return $result;
    }
}
