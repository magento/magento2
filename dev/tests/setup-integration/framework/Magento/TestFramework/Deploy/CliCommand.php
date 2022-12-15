<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Deploy;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Shell;
use Magento\Framework\Shell\CommandRenderer;
use Magento\Setup\Console\Command\InstallCommand;

/**
 * The purpose of this class is enable/disable module and upgrade commands execution.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CliCommand
{
    /**
     * @var Shell
     */
    private $shell;

    /**
     * @var TestModuleManager
     */
    private $testEnv;

    /**
     * @var ParametersHolder
     */
    private $parametersHolder;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param TestModuleManager $testEnv
     * @internal param Shell $shell
     */
    public function __construct(
        \Magento\TestFramework\Deploy\TestModuleManager $testEnv
    ) {
        $this->shell = new Shell(new CommandRenderer());
        $this->testEnv = $testEnv;
        $this->parametersHolder = new ParametersHolder();
    }

    /**
     * Copy Test module files and execute enable module command.
     *
     * @param string $moduleName
     * @return string
     * @throws LocalizedException
     */
    public function introduceModule($moduleName)
    {
        $this->testEnv->addModuleFiles($moduleName);
        return $this->enableModule($moduleName);
    }

    /**
     * Execute enable module command.
     *
     * @param string $moduleName
     * @return string
     * @throws LocalizedException
     */
    public function enableModule($moduleName)
    {
        $initParams = $this->parametersHolder->getInitParams();
        $enableModuleCommand = $this->getCliScriptCommand() . ' module:enable ' . $moduleName
            . ' -n -vvv --magento-init-params="' . $initParams['magento-init-params'] . '"';
        return $this->shell->execute($enableModuleCommand);
    }

    /**
     * Execute upgrade magento command.
     *
     * @param array $installParams
     * @return string
     * @throws LocalizedException
     */
    public function upgrade($installParams = [])
    {
        $initParams = $this->parametersHolder->getInitParams();
        $upgradeCommand = $this->getCliScriptCommandWithDI() . 'setup:upgrade -vvv -n --magento-init-params="'
            . $initParams['magento-init-params'] . '"';
        $installParams = $this->toCliArguments($installParams);
        $upgradeCommand .= ' ' . implode(" ", array_keys($installParams));

        return $this->shell->execute($upgradeCommand, array_values($installParams));
    }

    /**
     * Execute disable module command.
     *
     * @param string $moduleName
     * @return string
     * @throws LocalizedException
     */
    public function disableModule($moduleName)
    {
        $initParams = $this->parametersHolder->getInitParams();
        $disableModuleCommand = $this->getCliScriptCommand() . ' module:disable ' . $moduleName
            . ' -vvv --magento-init-params="' . $initParams['magento-init-params'] . '"';
        return $this->shell->execute($disableModuleCommand);
    }

    /**
     * Split quote db configuration.
     *
     * @return void
     * @throws LocalizedException
     * @deprecated split database solution is deprecated and will be removed
     */
    public function splitQuote()
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        trigger_error('Method is deprecated', E_USER_DEPRECATED);

        $initParams = $this->parametersHolder->getInitParams();
        $installParams = $this->toCliArguments(
            $this->parametersHolder->getDbData('checkout')
        );
        $command = $this->getCliScriptCommand() . ' setup:db-schema:split-quote ' .
            implode(" ", array_keys($installParams)) .
            ' -vvv  --no-interaction --magento-init-params="' .
            $initParams['magento-init-params'] . '"';

        $this->shell->execute($command, array_values($installParams));
    }

    /**
     * Split sales db configuration.
     *
     * @return void
     * @throws LocalizedException
     * @deprecated split database solution is deprecated and will be removed
     */
    public function splitSales()
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        trigger_error('Method is deprecated', E_USER_DEPRECATED);

        $initParams = $this->parametersHolder->getInitParams();
        $installParams = $this->toCliArguments(
            $this->parametersHolder->getDbData('sales')
        );
        $command = $this->getCliScriptCommand() . ' setup:db-schema:split-sales ' .
            implode(" ", array_keys($installParams)) .
            ' -vvv --magento-init-params="' .
            $initParams['magento-init-params'] . '"';

        $this->shell->execute($command, array_values($installParams));
    }

    /**
     * Clean all types of cache
     */
    public function cacheClean()
    {
        $initParams = $this->parametersHolder->getInitParams();
        $command = $this->getCliScriptCommand() . ' cache:clean ' .
            ' -vvv --magento-init-params=' .
            $initParams['magento-init-params'];

        $this->shell->execute($command);
    }

    /**
     * Uninstall module
     *
     * @param string $moduleName
     * @throws LocalizedException
     */
    public function uninstallModule($moduleName)
    {
        $initParams = $this->parametersHolder->getInitParams();
        $command = $this->getCliScriptCommand() . ' module:uninstall ' . $moduleName . ' --remove-data ' .
            ' -vvv --non-composer --magento-init-params="' .
            $initParams['magento-init-params'] . '"';

        $this->shell->execute($command);
    }

    /**
     * Convert from raw params to CLI arguments, like --admin-username.
     *
     * @param  array $params
     * @return array
     */
    private function toCliArguments(array $params)
    {
        $result = [];

        foreach ($params as $key => $value) {
            if (!empty($value)) {
                $result["--{$key}=%s"] = $value;
            }
        }

        return $result;
    }

    /**
     * Execute install command.
     *
     * @param array $modules
     * @param array $installParams
     * @return string
     * @throws \Exception
     */
    public function install(array $modules, array $installParams = [])
    {
        if (empty($modules)) {
            throw new \Exception("Cannot install Magento without modules");
        }

        $params = $this->parametersHolder->getInitParams();
        $installParams += [
            InstallCommand::INPUT_KEY_ENABLE_MODULES => implode(",", $modules),
            InstallCommand::INPUT_KEY_DISABLE_MODULES => 'all'
        ];
        $installParams = $this->toCliArguments(
            array_merge(
                $params,
                $this->parametersHolder->getDbData('default'),
                $installParams
            )
        );
        // run install script
        $exitCode = $this->shell->execute(
            PHP_BINARY . ' -f %s setup:install -vvv ' . implode(' ', array_keys($installParams)),
            array_merge([BP . '/bin/magento'], array_values($installParams))
        );
        $this->afterInstall();
        return $exitCode;
    }

    /**
     * You can decorate this function in order to add your own events here
     *
     * @return void
     */
    public function afterInstall()
    {
        //Take current deployment config in order to flush it cache after installation
        //Before installation usually we do not have any connections - so we need to add them
        $this->deploymentConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(DeploymentConfig::class);
        $this->deploymentConfig->resetData();
    }

    /**
     * Get custom magento-cli command with additional DI configuration
     *
     * @return string
     */
    private function getCliScriptCommandWithDI(): string
    {
        $params['MAGE_DIRS']['base']['path'] = BP;
        $params['INTEGRATION_TESTS_CLI_AUTOLOADER'] = TESTS_BASE_DIR . '/framework/autoload.php';
        $params['TESTS_BASE_DIR'] = TESTS_BASE_DIR;
        return 'INTEGRATION_TEST_PARAMS="' . urldecode(http_build_query($params)) . '"'
        . ' ' . PHP_BINARY . ' -f ' . INTEGRATION_TESTS_BASE_DIR
        . '/bin/magento ';
    }

    /**
     * Get basic magento-cli command
     *
     * @return string
     */
    private function getCliScriptCommand()
    {
        return PHP_BINARY . ' -f ' . BP . '/bin/magento ';
    }
}
