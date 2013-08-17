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
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Handles theme view.xml files
 */
class Mage_Core_Model_View_Config
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
     * @var Mage_Core_Model_Config_Modules_Reader
     */
    protected $_moduleReader;

    /**
     * @var Magento_Filesystem
     */
    protected $_filesystem;

    /**
     * @var Mage_Core_Model_View_Service
     */
    protected $_viewService;

    /**
     * View file system model
     *
     * @var Mage_Core_Model_View_FileSystem
     */
    protected $_viewFileSystem;

    /**
     * View config model
     *
     * @param Mage_Core_Model_Config_Modules_Reader $moduleReader
     * @param Magento_Filesystem $filesystem
     * @param Mage_Core_Model_View_Service $viewService
     * @param Mage_Core_Model_View_FileSystem $viewFileSystem
     */
    public function __construct(
        Mage_Core_Model_Config_Modules_Reader $moduleReader,
        Magento_Filesystem $filesystem,
        Mage_Core_Model_View_Service $viewService,
        Mage_Core_Model_View_FileSystem $viewFileSystem
    ) {
        $this->_moduleReader = $moduleReader;
        $this->_filesystem = $filesystem;
        $this->_viewService = $viewService;
        $this->_viewFileSystem = $viewFileSystem;
    }

    /**
     * Render view config object for current package and theme
     *
     * @param array $params
     * @return Magento_Config_View
     */
    public function getViewConfig(array $params = array())
    {
        $this->_viewService->updateDesignParams($params);
        /** @var $currentTheme Mage_Core_Model_Theme */
        $currentTheme = $params['themeModel'];
        $key = $currentTheme->getId();
        if (isset($this->_viewConfigs[$key])) {
            return $this->_viewConfigs[$key];
        }

        $configFiles = $this->_moduleReader->getModuleConfigurationFiles(Mage_Core_Model_Theme::FILENAME_VIEW_CONFIG);
        $themeConfigFile = $currentTheme->getCustomization()->getCustomViewConfigPath();
        if (empty($themeConfigFile) || !$this->_filesystem->has($themeConfigFile)) {
            $themeConfigFile = $this->_viewFileSystem->getFilename(
                Mage_Core_Model_Theme::FILENAME_VIEW_CONFIG, $params
            );
        }
        if ($themeConfigFile && $this->_filesystem->has($themeConfigFile)) {
            $configFiles[] = $themeConfigFile;
        }
        $config = new Magento_Config_View($configFiles);

        $this->_viewConfigs[$key] = $config;
        return $config;
    }
}
