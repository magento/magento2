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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme factory
 */
namespace Magento\Core\Model\Theme;

class FlyweightFactory
{
    /**
     * Object Manager
     *
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Core\Model\Theme[]
     */
    protected $_themes = array();

    /**
     * @var \Magento\Core\Model\Theme[]
     */
    protected $_themesByPath = array();

    /**
     * @param \Magento\ObjectManager $objectManager
     */
    public function __construct(\Magento\ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Creates or returns a shared model of theme
     *
     * @param string|int $themeKey
     * @param string $area
     * @return \Magento\Core\Model\Theme|null
     * @throws \InvalidArgumentException
     */
    public function create($themeKey, $area = \Magento\View\DesignInterface::DEFAULT_AREA)
    {
        if (is_numeric($themeKey)) {
            $themeModel = $this->_loadById($themeKey);
        } elseif (is_string($themeKey)) {
            $themeModel = $this->_loadByPath($themeKey, $area);
        } else {
            throw new \InvalidArgumentException('Incorrect theme identification key');
        }
        if (!$themeModel->getId()) {
            return null;
        }
        $this->_addTheme($themeModel);
        return $themeModel;
    }

    /**
     * Load theme by id
     *
     * @param int $themeId
     * @return \Magento\Core\Model\Theme
     */
    protected function _loadById($themeId)
    {
        if (isset($this->_themes[$themeId])) {
            return $this->_themes[$themeId];
        }

        /** @var $themeModel \Magento\View\Design\ThemeInterface */
        $themeModel = $this->_objectManager->create('Magento\View\Design\ThemeInterface');
        $themeModel->load($themeId);
        return $themeModel;
    }

    /**
     * Load theme by theme path
     *
     * @param string $themePath
     * @param string $area
     * @return \Magento\Core\Model\Theme
     */
    protected function _loadByPath($themePath, $area)
    {
        $fullPath = $area . \Magento\View\Design\ThemeInterface::PATH_SEPARATOR . $themePath;
        if (isset($this->_themesByPath[$fullPath])) {
            return $this->_themesByPath[$fullPath];
        }

        /** @var $themeCollection \Magento\Core\Model\Resource\Theme\Collection */
        $themeCollection = $this->_objectManager->create('Magento\Core\Model\Resource\Theme\Collection');
        $themeModel = $themeCollection->getThemeByFullPath($fullPath);
        return $themeModel;
    }

    /**
     * Add theme to shared collection
     *
     * @param \Magento\View\Design\ThemeInterface $themeModel
     * @return $this
     */
    protected function _addTheme(\Magento\View\Design\ThemeInterface $themeModel)
    {
        if ($themeModel->getId()) {
            $this->_themes[$themeModel->getId()] = $themeModel;
            $themePath = $themeModel->getFullPath();
            if ($themePath) {
                $this->_themesByPath[$themePath] = $themeModel;
            }
        }
        return $this;
    }
}
