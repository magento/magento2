<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\RequireJs;

/**
 * Provider of RequireJs config information
 */
class Config
{
    /**
     * Name of sub-directory where generated RequireJs config is placed
     */
    const DIR_NAME = '_requirejs';

    /**
     * File name of RequireJs config
     */
    const CONFIG_FILE_NAME = 'requirejs-config.js';

    /**
     * Path to normalization plugin in RequireJs format
     */
    const NORMALIZE_PLUGIN_PATH = 'mage/requirejs/plugin/id-normalizer';

    /**
     * Template for combined RequireJs config file
     */
    const FULL_CONFIG_TEMPLATE = <<<config
(function(require){
%function%

%usages%
})(require);
config;

    /**
     * Template for wrapped partial config
     */
    const PARTIAL_CONFIG_TEMPLATE = <<<config
(function() {
%config%
require.config(mageUpdateConfigPaths(config, '%context%'))
})();

config;


    /**
     * @var \Magento\Framework\RequireJs\Config\File\Collector\Aggregated
     */
    private $fileSource;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    private $design;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    private $baseDir;

    /**
     * @var \Magento\Framework\View\Asset\ContextInterface
     */
    private $staticContext;

    /**
     * @param \Magento\Framework\RequireJs\Config\File\Collector\Aggregated $fileSource
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\App\Filesystem $appFilesystem
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     */
    public function __construct(
        \Magento\Framework\RequireJs\Config\File\Collector\Aggregated $fileSource,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\App\Filesystem $appFilesystem,
        \Magento\Framework\View\Asset\Repository $assetRepo
    ) {
        $this->fileSource = $fileSource;
        $this->design = $design;
        $this->baseDir = $appFilesystem->getDirectoryRead(\Magento\Framework\App\Filesystem::ROOT_DIR);
        $this->staticContext = $assetRepo->getStaticViewFileContext();
    }

    /**
     * Get aggregated distributed configuration
     *
     * @return string
     */
    public function getConfig()
    {
        $functionSource = __DIR__ . '/paths-updater.js';
        $functionDeclaration = $this->baseDir->readFile($this->baseDir->getRelativePath($functionSource));

        $distributedConfig = '';
        $customConfigFiles = $this->fileSource->getFiles($this->design->getDesignTheme(), self::CONFIG_FILE_NAME);
        foreach ($customConfigFiles as $file) {
            $config = $this->baseDir->readFile($this->baseDir->getRelativePath($file->getFilename()));
            $distributedConfig .= str_replace(
                array('%config%', '%context%'),
                array($config, $file->getModule()),
                self::PARTIAL_CONFIG_TEMPLATE
            );
        }

        $fullConfig = str_replace(
            array('%function%', '%usages%'),
            array($functionDeclaration, $distributedConfig),
            self::FULL_CONFIG_TEMPLATE
        );

        return $fullConfig;
    }

    /**
     * Get path to config file relative to directory, where all config files with different context are located
     *
     * @return string
     */
    public function getConfigFileRelativePath()
    {
        return self::DIR_NAME . '/' . $this->staticContext->getPath() . '/' . self::CONFIG_FILE_NAME;
    }

    /**
     * Get base RequireJs configuration necessary for working with Magento application
     *
     * @return string
     */
    public function getBaseConfig()
    {
        $config = array(
            'baseUrl' => $this->staticContext->getBaseUrl() . $this->staticContext->getPath(),
            'paths' => array(
                'magento' => self::NORMALIZE_PLUGIN_PATH,
            ),
            //Disable the timeout, so that normalizer plugin and other JS modules are waited to be loaded
            // independent of server load time and network speed
            'waitSeconds' => 0,
        );
        $config = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return "require.config($config);\n";
    }
}
