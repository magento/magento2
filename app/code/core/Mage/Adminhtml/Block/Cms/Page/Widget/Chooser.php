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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * CMS page chooser for Wysiwyg CMS widget
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Cms_Page_Widget_Chooser extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Block construction, prepare grid params
     *
     * @param array $arguments Object data
     */
    protected function _construct()
    {
        parent::_construct();
        //$this->setDefaultSort('name');
        $this->setUseAjax(true);
        $this->setDefaultFilter(array('chooser_is_active' => '1'));
    }

    /**
     * Prepare chooser element HTML
     *
     * @param Varien_Data_Form_Element_Abstract $element Form Element
     * @return Varien_Data_Form_Element_Abstract
     */
    public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $uniqId = Mage::helper('Mage_Core_Helper_Data')->uniqHash($element->getId());
        $sourceUrl = $this->getUrl('*/cms_page_widget/chooser', array('uniq_id' => $uniqId));

        $chooser = $this->getLayout()->createBlock('Mage_Widget_Block_Adminhtml_Widget_Chooser')
            ->setElement($element)
            ->setTranslationHelper($this->getTranslationHelper())
            ->setConfig($this->getConfig())
            ->setFieldsetId($this->getFieldsetId())
            ->setSourceUrl($sourceUrl)
            ->setUniqId($uniqId);


        if ($element->getValue()) {
            $page = Mage::getModel('Mage_Cms_Model_Page')->load((int)$element->getValue());
            if ($page->getId()) {
                $chooser->setLabel($page->getTitle());
            }
        }

        $element->setData('after_element_html', $chooser->toHtml());
        return $element;
    }

    /**
     * Grid Row JS Callback
     *
     * @return string
     */
    public function getRowClickCallback()
    {
        $chooserJsObject = $this->getId();
        $js = '
            function (grid, event) {
                var trElement = Event.findElement(event, "tr");
                var pageTitle = trElement.down("td").next().innerHTML;
                var pageId = trElement.down("td").innerHTML.replace(/^\s+|\s+$/g,"");
                '.$chooserJsObject.'.setElementValue(pageId);
                '.$chooserJsObject.'.setElementLabel(pageTitle);
                '.$chooserJsObject.'.close();
            }
        ';
        return $js;
    }

    /**
     * Prepare pages collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('Mage_Cms_Model_Page')->getCollection();
        /* @var $collection Mage_Cms_Model_Resource_Page_Collection */
        $collection->setFirstStoreFlag(true);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare columns for pages grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('chooser_id', array(
            'header'    => Mage::helper('Mage_Cms_Helper_Data')->__('ID'),
            'align'     => 'right',
            'index'     => 'page_id',
            'width'     => 50
        ));

        $this->addColumn('chooser_title', array(
            'header'    => Mage::helper('Mage_Cms_Helper_Data')->__('Title'),
            'align'     => 'left',
            'index'     => 'title',
        ));

        $this->addColumn('chooser_identifier', array(
            'header'    => Mage::helper('Mage_Cms_Helper_Data')->__('URL Key'),
            'align'     => 'left',
            'index'     => 'identifier'
        ));

        $this->addColumn('chooser_root_template', array(
            'header'    => Mage::helper('Mage_Cms_Helper_Data')->__('Layout'),
            'index'     => 'root_template',
            'type'      => 'options',
            'options'   => Mage::getSingleton('Mage_Page_Model_Source_Layout')->getOptions(),
            'width'   => '100',
        ));

        $this->addColumn('chooser_is_active', array(
            'header'    => Mage::helper('Mage_Cms_Helper_Data')->__('Status'),
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => Mage::getModel('Mage_Cms_Model_Page')->getAvailableStatuses(),
            'width'     => '100',
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/cms_page_widget/chooser', array('_current' => true));
    }
}
