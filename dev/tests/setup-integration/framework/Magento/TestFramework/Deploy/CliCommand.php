<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Deploy;

use Magento\Framework\Shell;
use Magento\Framework\Shell\CommandRenderer;
use Magento\Setup\Console\Command\InstallCommand;

/**
 * The purpose of this class is enable/disable module and upgrade commands execution
 */
class CliCommand
{
    /**
     * @var \Magento\Framework\Shell
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
     * ShellCommand constructor.
     *
     * @param TestModuleManager $testEnv
     * @param ParametersHolder $paramatersHolder
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
     * Copy Test module files and execute enable module command
     *
     * @param string $moduleName
     * @return string
     */
    public function introduceModule($moduleName)
    {
        $this->testEnv->addModuleFiles($moduleName);
        return $this->enableModule($moduleName);
    }

    /**
     * Execute enable module command
     *
     * @param string $moduleName
     * @return string
     */
    public function enableModule($moduleName)
    {
        $initParams = $this->parametersHolder->getInitParams();
        $enableModuleCommand = 'php -f ' . BP . '/bin/magento module:enable ' . $moduleName
            . ' -n -vvv --magento-init-params=' . $initParams['magento-init-params'];
        return $this->shell->execute($enableModuleCommand);
    }

    /**
     * Execute upgrade magento command
     *
     * @return string
     */
    public function upgrade()
    {
        $initParams = $this->parametersHolder->getInitParams();
        $enableModuleCommand = 'php -f ' . BP . '/bin/magento setup:upgrade -vvv -n --magento-init-params='
            . $initParams['magento-init-params'];
        return $this->shell->execute($enableModuleCommand);
    }

    /**
     * Execute disable module command
     *
     * @param string $moduleName
     * @return string
     */
    public function disableModule($moduleName)
    {
        $initParams = $this->parametersHolder->getInitParams();
        $disableModuleCommand = 'php -f ' . BP . '/bin/magento module:disable Magento_'. $moduleName
            . ' -vvv --magento-init-params=' . $initParams['magento-init-params'];
        return $this->shell->execute($disableModuleCommand);
    }

    /**
     * Convert from raw params to CLI arguments, like --admin-username
     *
     * @param array $params
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
                $installParams,
                $this->parametersHolder->getDbData()
            )
        );
        // run install script
        return $this->shell->execute(
            PHP_BINARY . ' -f %s setup:install -vvv ' . implode(' ', array_keys($installParams)),
            array_merge([BP . '/bin/magento'], array_values($installParams))
        );
    }
}
