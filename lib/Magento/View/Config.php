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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\View;

/**
 * Handles theme view.xml files
 */
class Config implements \Magento\View\ConfigInterface
{
    /**
     * List of view configuration objects per theme
     *
     * @var array
     */
    protected $_viewConfigs = array();

    /**
     * Module configuration reader
     *
     * @var \Magento\Module\Dir\Reader
     */
    protected $_moduleReader;

    /**
     * @var \Magento\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\View\Service
     */
    protected $_viewService;

    /**
     * View file system model
     *
     * @var \Magento\View\FileSystem
     */
    protected $_viewFileSystem;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @param \Magento\Module\Dir\Reader $moduleReader
     * @param \Magento\Filesystem $filesystem
     * @param Service $viewService
     * @param FileSystem $viewFileSystem
     * @param string $filename
     */
    public function __construct(
        \Magento\Module\Dir\Reader $moduleReader,
        \Magento\Filesystem $filesystem,
        \Magento\View\Service $viewService,
        \Magento\View\FileSystem $viewFileSystem,
        $filename = self::CONFIG_FILE_NAME
    ) {
        $this->_moduleReader = $moduleReader;
        $this->_filesystem = $filesystem;
        $this->_viewService = $viewService;
        $this->_viewFileSystem = $viewFileSystem;
        $this->filename = $filename;
    }

    /**
     * Render view config object for current package and theme
     *
     * @param array $params
     * @return \Magento\Config\View
     */
    public function getViewConfig(array $params = array())
    {
        $this->_viewService->updateDesignParams($params);
        /** @var $currentTheme \Magento\View\Design\ThemeInterface */
        $currentTheme = $params['themeModel'];
        $key = $currentTheme->getId();
        if (isset($this->_viewConfigs[$key])) {
            return $this->_viewConfigs[$key];
        }

        $configFiles = $this->_moduleReader->getConfigurationFiles($this->filename);
        $themeConfigFile = $currentTheme->getCustomization()->getCustomViewConfigPath();
        if (empty($themeConfigFile) || !$this->_filesystem->has($themeConfigFile)) {
            $themeConfigFile = $this->_viewFileSystem->getFilename(
                $this->filename, $params
            );
        }
        if ($themeConfigFile && $this->_filesystem->has($themeConfigFile)) {
            $configFiles[] = $themeConfigFile;
        }
        $config = new \Magento\Config\View($configFiles);

        $this->_viewConfigs[$key] = $config;
        return $config;
    }
}
