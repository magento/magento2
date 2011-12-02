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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * description
 *
 * @category    Mage
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Search_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Init Grid default properties
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('catalog_search_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection for Grid
     *
     * @return Mage_Adminhtml_Block_Catalog_Search_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('Mage_CatalogSearch_Model_Query')
            ->getResourceCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare Grid columns
     *
     * @return Mage_Adminhtml_Block_Catalog_Search_Grid
     */
    protected function _prepareColumns()
    {
        /*$this->addColumn('query_id', array(
            'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('ID'),
            'width'     => '50px',
            'index'     => 'query_id',
        ));*/

        $this->addColumn('search_query', array(
            'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Search Query'),
            'index'     => 'query_text',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        => Mage::helper('Mage_Catalog_Helper_Data')->__('Store'),
                'index'         => 'store_id',
                'type'          => 'store',
                'store_view'    => true,
                'sortable'      => false
            ));
        }

        $this->addColumn('num_results', array(
            'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Results'),
            'index'     => 'num_results',
            'type'      => 'number'
        ));

        $this->addColumn('popularity', array(
            'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Number of Uses'),
            'index'     => 'popularity',
            'type'      => 'number'
        ));

        $this->addColumn('synonym_for', array(
            'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Synonym For'),
            'align'     => 'left',
            'index'     => 'synonym_for',
            'width'     => '160px'
        ));

        $this->addColumn('redirect', array(
            'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Redirect'),
            'align'     => 'left',
            'index'     => 'redirect',
            'width'     => '200px'
        ));

        $this->addColumn('display_in_terms', array(
            'header'=>Mage::helper('Mage_Catalog_Helper_Data')->__('Display in Suggested Terms'),
            'sortable'=>true,
            'index'=>'display_in_terms',
            'type' => 'options',
            'width' => '100px',
            'options' => array(
                '1' => Mage::helper('Mage_Catalog_Helper_Data')->__('Yes'),
                '0' => Mage::helper('Mage_Catalog_Helper_Data')->__('No'),
            ),
            'align' => 'left',
        ));
        $this->addColumn('action',
            array(
                'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Action'),
                'width'     => '100px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(array(
                    'caption'   => Mage::helper('Mage_Catalog_Helper_Data')->__('Edit'),
                    'url'       => array(
                        'base'=>'*/*/edit'
                    ),
                    'field'   => 'id'
                )),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'catalog',
        ));
        return parent::_prepareColumns();
    }

    /**
     * Prepare grid massaction actions
     *
     * @return Mage_Adminhtml_Block_Catalog_Search_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('query_id');
        $this->getMassactionBlock()->setFormFieldName('search');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('Mage_Catalog_Helper_Data')->__('Are you sure?')
        ));

        return parent::_prepareMassaction();
    }

    /**
     * Retrieve Row Click callback URL
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
