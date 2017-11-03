<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Deploy;

use Magento\Framework\App\Filesystem\DirectoryList;

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
     * @var array
     */
    private $initParams;

    /**
     * @var TestModuleManager
     */
    private $testEnv;

    /**
     * ShellCommand constructor.
     *
     * @param \Magento\Framework\Shell $shell
     * @param TestModuleManager $testEnv
     */
    public function __construct(
        \Magento\Framework\Shell $shell,
        \Magento\TestFramework\Deploy\TestModuleManager $testEnv
    ) {
        $this->shell = $shell;
        $this->testEnv = $testEnv;
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
        $initParams = $this->getInitParams();
        $enableModuleCommand = 'php -f ' . BP . '/bin/magento module:enable Magento_' . $moduleName
            . ' -n -vvv --magento-init-params=' . $initParams;
        return $this->shell->execute($enableModuleCommand);
    }

    /**
     * Execute upgrade magento command
     *
     * @return string
     */
    public function upgrade()
    {
        $initParams = $this->getInitParams();
        $enableModuleCommand = 'php -f ' . BP . '/bin/magento setup:upgrade -vvv -n --magento-init-params='
            . $initParams;
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
        $initParams = $this->getInitParams();
        $disableModuleCommand = 'php -f ' . BP . '/bin/magento module:disable Magento_'. $moduleName
            . ' -vvv --magento-init-params=' . $initParams;
        return $this->shell->execute($disableModuleCommand);
    }

    /**
     * Return application initialization parameters
     *
     * @return array
     */
    private function getInitParams()
    {
        if (!isset($this->initParams)) {
            $testsBaseDir = dirname(__DIR__);
            $settings = new \Magento\TestFramework\Bootstrap\Settings($testsBaseDir, get_defined_constants());
            $appMode = $settings->get('TESTS_MAGENTO_MODE');
            $customDirs = $this->getCustomDirs();
            $initParams = [
                \Magento\Framework\App\Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS => $customDirs,
                \Magento\Framework\App\State::PARAM_MODE => $appMode
            ];
            $this->initParams = urldecode(http_build_query($initParams));
        }
        return $this->initParams;
    }

    /**
     * Get customized directory paths
     *
     * @return array
     */
    private function getCustomDirs()
    {
        $installDir = TESTS_TEMP_DIR;
        $path = DirectoryList::PATH;
        $customDirs = [
            DirectoryList::CONFIG => [$path => "{$installDir}/etc"],
        ];
        return $customDirs;
    }
}
