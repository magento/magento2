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
namespace Magento\Framework\View;

use Magento\Framework\Filesystem\Directory\ReadInterface;

/**
 * Handles theme view.xml files
 */
class Config implements \Magento\Framework\View\ConfigInterface
{
    /**
     * List of view configuration objects per theme
     *
     * @var array
     */
    protected $viewConfigs = array();

    /**
     * Module configuration reader
     *
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $moduleReader;

    /**
     * Root directory
     *
     * @var ReadInterface
     */
    protected $rootDirectory;

    /**
     * View service
     *
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * View file system model
     *
     * @var \Magento\Framework\View\FileSystem
     */
    protected $viewFileSystem;

    /**
     * File name
     *
     * @var string
     */
    protected $filename;

    /**
     * File iterator factory
     *
     * @var \Magento\Framework\Config\FileIteratorFactory
     */
    protected $fileIteratorFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\View\FileSystem $viewFileSystem
     * @param \Magento\Framework\Config\FileIteratorFactory $fileIteratorFactory
     * @param string $filename
     */
    public function __construct(
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\App\Filesystem $filesystem,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\FileSystem $viewFileSystem,
        \Magento\Framework\Config\FileIteratorFactory $fileIteratorFactory,
        $filename = self::CONFIG_FILE_NAME
    ) {
        $this->moduleReader = $moduleReader;
        $this->rootDirectory = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem::ROOT_DIR);
        $this->assetRepo = $assetRepo;
        $this->viewFileSystem = $viewFileSystem;
        $this->filename = $filename;
        $this->fileIteratorFactory = $fileIteratorFactory;
    }

    /**
     * Render view config object for current package and theme
     *
     * @param array $params
     * @return \Magento\Framework\Config\View
     */
    public function getViewConfig(array $params = array())
    {
        $this->assetRepo->updateDesignParams($params);
        /** @var $currentTheme \Magento\Framework\View\Design\ThemeInterface */
        $currentTheme = $params['themeModel'];
        $key = $currentTheme->getId();
        if (isset($this->viewConfigs[$key])) {
            return $this->viewConfigs[$key];
        }

        $configFiles = $this->moduleReader->getConfigurationFiles(basename($this->filename))->toArray();
        $themeConfigFile = $currentTheme->getCustomization()->getCustomViewConfigPath();
        if (empty($themeConfigFile)
            || !$this->rootDirectory->isExist($this->rootDirectory->getRelativePath($themeConfigFile))
        ) {
            $themeConfigFile = $this->viewFileSystem->getFilename($this->filename, $params);
        }
        if ($themeConfigFile
            && $this->rootDirectory->isExist($this->rootDirectory->getRelativePath($themeConfigFile))
        ) {
            $configFiles[$this->rootDirectory->getRelativePath($themeConfigFile)] = $this->rootDirectory->readFile(
                $this->rootDirectory->getRelativePath($themeConfigFile)
            );
        }
        $config = new \Magento\Framework\Config\View($configFiles);

        $this->viewConfigs[$key] = $config;
        return $config;
    }
}
