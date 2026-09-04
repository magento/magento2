<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\TestFramework;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DriverPool;

/**
 * Provides access to the application for the tests
 *
 * Allows installation and uninstallation
 */
class WebApiApplication extends Application
{
    /**
     * @inheritdoc
     */
    public function run()
    {
        throw new \Exception(
            "Can't start application: purpose of Web API Application is to use classes and models from the application"
            . " and don't run it"
        );
    }

    /**
     * @inheritdoc
     */
    public function install($cleanup)
    {
        $this->assertInstallationTargetsConfiguredDatabase();

        if ($cleanup) {
            $this->cleanup();
        }

        $installOptions = $this->getInstallConfig();

        /* Install application */
        if ($installOptions) {
            $installCmd = 'php -f ' . BP . '/bin/magento setup:install -vvv';
            $installArgs = [];
            foreach ($installOptions as $optionName => $optionValue) {
                if (is_bool($optionValue)) {
                    if (true === $optionValue) {
                        $installCmd .= " --$optionName";
                    }
                    continue;
                }
                $installCmd .= " --$optionName=%s";
                $installArgs[] = $optionValue;
            }
            $this->_shell->execute($installCmd, $installArgs);
        }
        /* Set Indexer mode as "Update on Save" & Reindex all the Indexers */
        $this->_shell->execute(
            'php -f ' . BP . '/bin/magento indexer:set-mode realtime -vvv'
        );
        $this->_shell->execute(
            'php -f ' . BP . '/bin/magento indexer:reindex -vvv'
        );

        $this->runPostInstallCommands();
    }

    /**
     * @inheritdoc
     *
     * Return empty array of custom directories
     * @return array
     */
    protected function getCustomDirs()
    {
        return [];
    }

    /**
     * Verify that the installation is not about to destroy a database the tests are not configured to use.
     *
     * Unlike the integration tests framework, Web API functional tests do not have their own configuration
     * directory - they reuse the "app/etc" of the installation that serves TESTS_BASE_URL. Both
     * "setup:uninstall" and "setup:install" therefore operate on whatever "app/etc/env.php" points at. When
     * that database differs from the one declared in TESTS_INSTALL_CONFIG_FILE, the installation silently
     * drops all tables of an unrelated database instead of the configured test one.
     *
     * @return void
     * @throws LocalizedException
     */
    private function assertInstallationTargetsConfiguredDatabase(): void
    {
        if (!$this->isInstalled()) {
            return;
        }

        $configuredDbName = $this->getInstallConfig()['db-name'] ?? null;
        $installedDbName = $this->getInstalledDbName();

        if (!$configuredDbName || !$installedDbName || $configuredDbName === $installedDbName) {
            return;
        }

        throw new LocalizedException(
            __(
                'Web API functional tests are configured to install Magento into the "%1" database, but the '
                . 'installation in "%2" uses the "%3" database. Installing would drop all tables of "%3" and '
                . 'overwrite its deployment configuration, because Web API functional tests reuse the '
                . 'application they send requests to. Either configure the test installation to use the "%3" '
                . 'database, or set TESTS_MAGENTO_INSTALLATION to "disabled" to run the tests against the '
                . 'already installed application.',
                $configuredDbName,
                $this->_configDir,
                $installedDbName
            )
        );
    }

    /**
     * Retrieve the database name of the already installed application, if its deployment configuration exists.
     *
     * @return string|null
     */
    private function getInstalledDbName(): ?string
    {
        $configFilePool = new ConfigFilePool();
        $envFile = $this->_configDir . '/' . $configFilePool->getPath(ConfigFilePool::APP_ENV);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (!file_exists($envFile)) {
            return null;
        }

        $reader = new Reader($this->dirList, new DriverPool(), $configFilePool);
        $deploymentConfig = new DeploymentConfig($reader, []);

        return $deploymentConfig->get(
            ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT
            . '/' . ConfigOptionsListConstants::KEY_NAME
        );
    }
}
