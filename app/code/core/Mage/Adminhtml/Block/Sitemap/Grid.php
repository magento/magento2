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
 * Sitemaps grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Sitemap_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('sitemapGrid');
        $this->setDefaultSort('sitemap_id');

    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('Mage_Sitemap_Model_Sitemap')->getCollection();
        /* @var $collection Mage_Sitemap_Model_Resource_Sitemap_Collection */
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('sitemap_id', array(
            'header'    => Mage::helper('Mage_Sitemap_Helper_Data')->__('ID'),
            'width'     => '50px',
            'index'     => 'sitemap_id'
        ));

        $this->addColumn('sitemap_filename', array(
            'header'    => Mage::helper('Mage_Sitemap_Helper_Data')->__('Filename'),
            'index'     => 'sitemap_filename'
        ));

        $this->addColumn('sitemap_path', array(
            'header'    => Mage::helper('Mage_Sitemap_Helper_Data')->__('Path'),
            'index'     => 'sitemap_path'
        ));

        $this->addColumn('link', array(
            'header'    => Mage::helper('Mage_Sitemap_Helper_Data')->__('Link for Google'),
            'index'     => 'concat(sitemap_path, sitemap_filename)',
            'renderer'  => 'Mage_Adminhtml_Block_Sitemap_Grid_Renderer_Link',
        ));

        $this->addColumn('sitemap_time', array(
            'header'    => Mage::helper('Mage_Sitemap_Helper_Data')->__('Last Time Generated'),
            'width'     => '150px',
            'index'     => 'sitemap_time',
            'type'      => 'datetime',
        ));


        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('Mage_Sitemap_Helper_Data')->__('Store View'),
                'index'     => 'store_id',
                'type'      => 'store',
            ));
        }

        $this->addColumn('action', array(
            'header'   => Mage::helper('Mage_Sitemap_Helper_Data')->__('Action'),
            'filter'   => false,
            'sortable' => false,
            'width'    => '100',
            'renderer' => 'Mage_Adminhtml_Block_Sitemap_Grid_Renderer_Action'
        ));

        return parent::_prepareColumns();
    }

    /**
     * Row click url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('sitemap_id' => $row->getId()));
    }

}
