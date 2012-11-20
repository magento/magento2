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
 * @package     Mage_Theme
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Themes grid
 */
class Mage_Theme_Block_Adminhtml_System_Design_Theme_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Init Grid properties
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('theme_grid');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Prepare grid data collection
     *
     * @return Mage_Theme_Block_Adminhtml_System_Design_Theme_Grid|Mage_Backend_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        /** @var $collection Mage_Core_Model_Resource_Theme_Collection */
        $collection = Mage::getResourceModel('Mage_Core_Model_Resource_Theme_Collection');
        $collection->addParentTitle();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * Define grid columns
     *
     * @return Mage_Adminhtml_Block_System_Design_Grid|Mage_Backend_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('theme_title', array(
            'header'       => $this->__('Theme Title'),
            'index'        => 'theme_title',
            'filter_index' => 'main_table.theme_title',
        ));

        $this->addColumn('parent_theme_title', array(
            'header'       => $this->__('Parent Theme'),
            'index'        => 'parent_theme_title',
            'filter_index' => 'parent.theme_title'
        ));

        $this->addColumn('theme_path', array(
            'header'       => $this->__('Theme Path'),
            'index'        => 'theme_path',
            'filter_index' => 'main_table.theme_path'
        ));

        $this->addColumn('theme_version', array(
            'header'       => $this->__('Theme Version'),
            'index'        => 'theme_version',
            'filter_index' => 'main_table.theme_version'
        ));

        $this->addColumn('magento_version_from', array(
            'header'       => $this->__('Magento Version From'),
            'index'        => 'magento_version_from',
            'filter_index' => 'main_table.magento_version_from'
        ));

        $this->addColumn('magento_version_to', array(
            'header'       => $this->__('Magento Version To'),
            'index'        => 'magento_version_to',
            'filter_index' => 'main_table.magento_version_to'
        ));

        return parent::_prepareColumns();
    }

    /**
     * Prepare row click url
     *
     * @param Varien_Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /**
     * Prepare grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}
