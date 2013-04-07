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

class Mage_Core_Model_Design_Package_Proxy implements Mage_Core_Model_Design_PackageInterface
{
    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Mage_Core_Model_Design_Package
     */
    protected $_model;

    /**
     * @param Magento_ObjectManager $objectManager
     */
    public function __construct(Magento_ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * @return Mage_Core_Model_Design_Package
     */
    protected function _getInstance()
    {
        if (null === $this->_model) {
            $this->_model = $this->_objectManager->get('Mage_Core_Model_Design_Package');
        }
        return $this->_model;
    }

    /**
     * Set package area
     *
     * @param string $area
     * @return Mage_Core_Model_Design_PackageInterface
     */
    public function setArea($area)
    {
        return $this->_getInstance()->setArea($area);
    }

    /**
     * Retrieve package area
     *
     * @return string
     */
    public function getArea()
    {
        return $this->_getInstance()->getArea();
    }


    /**
     * Set theme path
     *
     * @param Mage_Core_Model_Theme|int|string $theme
     * @param string $area
     * @return Mage_Core_Model_Design_PackageInterface
     */
    public function setDesignTheme($theme, $area = null)
    {
        return $this->_getInstance()->setDesignTheme($theme, $area);
    }


    /**
     * Get default theme which declared in configuration
     *
     * @param string $area
     * @param array $params
     * @return string|int
     */
    public function getConfigurationDesignTheme($area = null, array $params = array())
    {
        return $this->_getInstance()->getConfigurationDesignTheme($area, $params);
    }

    /**
     * Set default design theme
     *
     * @return Mage_Core_Model_Design_PackageInterface
     */
    public function setDefaultDesignTheme()
    {
        return $this->_getInstance()->setDefaultDesignTheme();
    }

    /**
     * Design theme model getter
     *
     * @return Mage_Core_Model_Theme
     */
    public function getDesignTheme()
    {
        return $this->_getInstance()->getDesignTheme();
    }

    /**
     * Get existing file name with fallback to default
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    public function getFilename($file, array $params = array())
    {
        return $this->_getInstance()->getFilename($file, $params);
    }

    /**
     * Get a locale file
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    public function getLocaleFileName($file, array $params = array())
    {
        return $this->_getInstance()->getLocaleFileName($file, $params);
    }

    /**
     * Find a view file using fallback mechanism
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    public function getViewFile($file, array $params = array())
    {
        return $this->_getInstance()->getViewFile($file, $params);
    }

    /**
     * Remove all merged js/css files
     *
     * @return bool
     */
    public function cleanMergedJsCss()
    {
        return $this->_getInstance()->cleanMergedJsCss();
    }

    /**
     * Get url to file base on theme file identifier.
     * Publishes file there, if needed.
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    public function getViewFileUrl($file, array $params = array())
    {
        return $this->_getInstance()->getViewFileUrl($file, $params);
    }

    /**
     * Get url to public file
     *
     * @param string $file
     * @param bool|null $isSecure
     * @return string
     * @throws Magento_Exception
     */
    public function getPublicFileUrl($file, $isSecure = null)
    {
        return $this->_getInstance()->getPublicFileUrl($file, $isSecure);
    }

    /**
     * Return directory for theme files publication
     *
     * @return string
     */
    public function getPublicDir()
    {
        return $this->_getInstance()->getPublicDir();
    }

    /**
     * Return whether view files merging is allowed or not
     *
     * @return bool
     */
    public function isMergingViewFilesAllowed()
    {
        return $this->_getInstance()->isMergingViewFilesAllowed();
    }

    /**
     * Merge files, located under the same folder, into one and return file name of merged file
     *
     * @param array $files list of names relative to the same folder
     * @param string $contentType
     * @return string
     * @throws Magento_Exception if not existing file requested for merge
     */
    public function mergeFiles($files, $contentType)
    {
        return $this->_getInstance()->mergeFiles($files, $contentType);
    }

    /**
     * Render view config object for current package and theme
     *
     * @return Magento_Config_View
     */
    public function getViewConfig()
    {
        return $this->_getInstance()->getViewConfig();
    }
}
