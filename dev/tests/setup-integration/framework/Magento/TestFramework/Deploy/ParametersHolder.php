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
 * The purpose of this class is enable/disable module and upgrade commands execution.
 */
class ParametersHolder
{
    /**
     * Initialize params.
     *
     * @var array
     */
    private $initParams;

    /**
     * Return application initialization parameters.
     *
     * @return array
     */
    public function getInitParams()
    {
        if (!isset($this->initParams)) {
            $customDirs = $this->getCustomDirs();
            $initParams = [
                \Magento\Framework\App\Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS => $customDirs,
            ];
            $this->initParams = ['magento-init-params' => urldecode(http_build_query($initParams))];
        }
        return $this->initParams;
    }

    /**
     * Include data from config file and convert it to db format:
     * -db-name
     * -db-user-name
     * -db-password
     * -db-host
     *
     * @param  string $resource can be default, checkout, sales
     * @return array
     */
    public function getDbData($resource)
    {
        $dbData = include TESTS_INSTALLATION_DB_CONFIG_FILE;
        return $dbData[$resource];
    }

    /**
     * Get customized directory paths.
     *
     * @return array
     */
    private function getCustomDirs()
    {
        $installDir = TESTS_TEMP_DIR;
        $path = DirectoryList::PATH;
        $var = "{$installDir}/var";
        $customDirs = [
            DirectoryList::CONFIG => [$path => "{$installDir}/etc"],
            DirectoryList::VAR_DIR => [$path => $var],
        ];
        return $customDirs;
    }
}
