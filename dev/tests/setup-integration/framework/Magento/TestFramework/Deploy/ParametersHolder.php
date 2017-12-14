<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Deploy;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Shell;
use Magento\Setup\Console\Command\InstallCommand;

/**
 * The purpose of this class is enable/disable module and upgrade commands execution
 */
class ParametersHolder
{
    /**
     * @var array
     */
    private $initParams;

    /**
     * Return application initialization parameters
     *
     * @return array
     */
    public function getInitParams()
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
            $this->initParams = ['magento-init-params' => urldecode(http_build_query($initParams))];
        }
        return $this->initParams;
    }

    /**
     * Include data from config file and convert it to db format
     * -db-name
     * -db-user-name
     * -db-password
     * -db-host
     *
     * @return array
     */
    public function getDbData()
    {
        return include TESTS_INSTALL_CONFIG_FILE;
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
