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
 * Theme collection
 */
class Mage_Core_Model_Resource_Theme_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Default page size
     */
    const DEFAULT_PAGE_SIZE = 4;

    /**
     * Collection initialization
     */
    protected function _construct()
    {
        $this->_init('Mage_Core_Model_Theme', 'Mage_Core_Model_Resource_Theme');
    }

    /**
     * Add title for parent themes
     *
     * @return Mage_Core_Model_Resource_Theme_Collection
     */
    public function addParentTitle()
    {
        $this->getSelect()->joinLeft(
            array('parent' => $this->getMainTable()),
            'main_table.parent_id = parent.theme_id',
            array('parent_theme_title' => 'parent.theme_title')
        );
        return $this;
    }

    /**
     * Add area filter
     *
     * @param string $area
     * @return Mage_Core_Model_Resource_Theme_Collection
     */
    public function addAreaFilter($area = Mage_Core_Model_App_Area::AREA_FRONTEND)
    {
        $this->getSelect()->where('main_table.area=?', $area);
        return $this;
    }

    /**
     * Return array for select field
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('theme_id', 'theme_title');
    }

    /**
     * Return array for grid column
     *
     * @return array
     */
    public function toOptionHash()
    {
        return $this->_toOptionHash('theme_id', 'theme_title');
    }

    /**
     * Check whether all themes have non virtual parent theme
     *
     * @return Mage_Core_Model_Resource_Theme_Collection
     */
    public function checkParentInThemes()
    {
        /** @var $theme Mage_Core_Model_Theme */
        foreach ($this as $theme) {
            if ($theme->getParentId()) {
                $newParentId = $this->_getParentThemeRecursively($theme->getParentId());
                if ($newParentId != $theme->getParentId()) {
                    $theme->setParentId($newParentId);
                    $theme->save();
                }
            }
        }
        return $this;
    }

    /**
     * Get parent non virtual theme recursively
     *
     * @param int $parentId
     * @return int|null
     */
    protected function _getParentThemeRecursively($parentId)
    {
        /** @var $parentTheme Mage_Core_Model_Theme */
        $parentTheme = $this->getItemById($parentId);
        if (!$parentTheme->getId() || ($parentTheme->isVirtual() && !$parentTheme->getParentId())) {
            $parentId = null;
        } else if ($parentTheme->isVirtual()) {
            $parentId = $this->_getParentThemeRecursively($parentTheme->getParentId());
        }
        return $parentId;
    }

    /**
     * Get theme from DB by area and theme_path
     *
     * @param string $fullPath
     * @return Mage_Core_Model_Theme
     */
    public function getThemeByFullPath($fullPath)
    {
        list($area, $themePath) = explode('/', $fullPath, 2);
        $this->addFieldToFilter('area', $area);
        $this->addFieldToFilter('theme_path', $themePath);

        return $this->getFirstItem();
    }

    /**
     * Set page size
     *
     * @param int $size
     * @return Mage_Core_Model_Resource_Theme_Collection
     */
    public function setPageSize($size = self::DEFAULT_PAGE_SIZE)
    {
        return parent::setPageSize($size);
    }

    /**
     * Update all child themes relations
     *
     * @param Mage_Core_Model_Theme $themeModel
     * @return Mage_Core_Model_Resource_Theme_Collection
     */
    public function updateChildRelations(Mage_Core_Model_Theme $themeModel)
    {
        $parentThemeId = $themeModel->getParentId();
        $this->addFieldToFilter('parent_id', array('eq' => $themeModel->getId()))->load();

        /** @var $theme Mage_Core_Model_Theme */
        foreach ($this->getItems() as $theme) {
            $theme->setParentId($parentThemeId)->save();
        }
        return $this;
    }
}
