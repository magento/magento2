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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml reviews grid
 *
 * @method int getProductId() getProductId()
 * @method Mage_Adminhtml_Block_Review_Grid setProductId() setProductId(int $productId)
 * @method int getCustomerId() getCustomerId()
 * @method Mage_Adminhtml_Block_Review_Grid setCustomerId() setCustomerId(int $customerId)
 * @method Mage_Adminhtml_Block_Review_Grid setMassactionIdFieldOnlyIndexValue() setMassactionIdFieldOnlyIndexValue(bool $onlyIndex)
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Review_Grid extends Mage_Backend_Block_Widget_Grid_Extended
{
    /**
     * Initialize grid
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('reviwGrid');
        $this->setDefaultSort('created_at');
    }

    /**
     * Save search results
     *
     * @return Mage_Backend_Block_Widget_Grid
     */
    protected function _afterLoadCollection()
    {
        /** @var $actionPager Mage_Review_Helper_Action_Pager */
        $actionPager = Mage::helper('Mage_Review_Helper_Action_Pager');
        $actionPager->setStorageId('reviews');
        $actionPager->setItems($this->getCollection()->getResultingIds());

        return parent::_afterLoadCollection();
    }

    /**
     * Prepare collection
     *
     * @return Mage_Adminhtml_Block_Review_Grid
     */
    protected function _prepareCollection()
    {
        /** @var $model Mage_Review_Model_Review */
        $model = Mage::getModel('Mage_Review_Model_Review');
        /** @var $collection Mage_Review_Model_Resource_Review_Product_Collection */
        $collection = $model->getProductCollection();

        if ($this->getProductId() || $this->getRequest()->getParam('productId', false)) {
            $productId = $this->getProductId();
            if (!$productId) {
                $productId = $this->getRequest()->getParam('productId');
            }
            $this->setProductId($productId);
            $collection->addEntityFilter($this->getProductId());
        }

        if ($this->getCustomerId() || $this->getRequest()->getParam('customerId', false)) {
            $customerId = $this->getCustomerId();
            if (!$customerId){
                $customerId = $this->getRequest()->getParam('customerId');
            }
            $this->setCustomerId($customerId);
            $collection->addCustomerFilter($this->getCustomerId());
        }

        if (Mage::registry('usePendingFilter') === true) {
            $collection->addStatusFilter($model->getPendingStatus());
        }

        $collection->addStoreData();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return Mage_Backend_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        /** @var $helper Mage_Review_Helper_Data */
        $helper = Mage::helper('Mage_Review_Helper_Data');
        $this->addColumn('review_id', array(
            'header'        => $helper->__('ID'),
            'align'         => 'right',
            'width'         => '50px',
            'filter_index'  => 'rt.review_id',
            'index'         => 'review_id',
        ));

        $this->addColumn('created_at', array(
            'header'        => $helper->__('Created'),
            'align'         => 'left',
            'type'          => 'datetime',
            'width'         => '100px',
            'filter_index'  => 'rt.created_at',
            'index'         => 'review_created_at',
        ));

        if( !Mage::registry('usePendingFilter') ) {
            $this->addColumn('status', array(
                'header'        => $helper->__('Status'),
                'align'         => 'left',
                'type'          => 'options',
                'options'       => $helper->getReviewStatuses(),
                'width'         => '100px',
                'filter_index'  => 'rt.status_id',
                'index'         => 'status_id',
            ));
        }

        $this->addColumn('title', array(
            'header'        => $helper->__('Title'),
            'align'         => 'left',
            'width'         => '100px',
            'filter_index'  => 'rdt.title',
            'index'         => 'title',
            'type'          => 'text',
            'truncate'      => 50,
            'escape'        => true,
        ));

        $this->addColumn('nickname', array(
            'header'        => $helper->__('Nickname'),
            'align'         => 'left',
            'width'         => '100px',
            'filter_index'  => 'rdt.nickname',
            'index'         => 'nickname',
            'type'          => 'text',
            'truncate'      => 50,
            'escape'        => true,
        ));

        $this->addColumn('detail', array(
            'header'        => $helper->__('Review'),
            'align'         => 'left',
            'index'         => 'detail',
            'filter_index'  => 'rdt.detail',
            'type'          => 'text',
            'truncate'      => 50,
            'nl2br'         => true,
            'escape'        => true,
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('visible_in', array(
                'header'    => $helper->__('Visibility'),
                'index'     => 'stores',
                'type'      => 'store',
                'store_view' => true,
            ));
        }

        $this->addColumn('type', array(
            'header'    => $helper->__('Type'),
            'type'      => 'select',
            'index'     => 'type',
            'filter'    => 'Mage_Adminhtml_Block_Review_Grid_Filter_Type',
            'renderer'  => 'Mage_Adminhtml_Block_Review_Grid_Renderer_Type'
        ));

        $this->addColumn('name', array(
            'header'    => $helper->__('Product'),
            'align'     =>'left',
            'type'      => 'text',
            'index'     => 'name',
            'escape'    => true
        ));

        $this->addColumn('sku', array(
            'header'    => $helper->__('SKU'),
            'align'     => 'right',
            'type'      => 'text',
            'width'     => '50px',
            'index'     => 'sku',
            'escape'    => true
        ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getReviewId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Edit'),
                        'url'     => array(
                            'base'=>'*/catalog_product_review/edit',
                            'params'=> array(
                                'productId' => $this->getProductId(),
                                'customerId' => $this->getCustomerId(),
                                'ret'       => ( Mage::registry('usePendingFilter') ) ? 'pending' : null
                            )
                         ),
                         'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false
        ));

        $this->addRssList('rss/catalog/review', Mage::helper('Mage_Catalog_Helper_Data')->__('Pending Reviews RSS'));

        return parent::_prepareColumns();
    }

    /**
     * Prepare grid mass actions
     *
     * @return Mage_Backend_Block_Widget_Grid|void
     */
    protected function _prepareMassaction()
    {
        /** @var $helper Mage_Review_Helper_Data */
        $helper = Mage::helper('Mage_Review_Helper_Data');

        $this->setMassactionIdField('review_id');
        $this->setMassactionIdFilter('rt.review_id');
        $this->setMassactionIdFieldOnlyIndexValue(true);
        $this->getMassactionBlock()->setFormFieldName('reviews');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'=> Mage::helper('Mage_Review_Helper_Data')->__('Delete'),
            'url'  => $this->getUrl(
                '*/*/massDelete',
                array('ret' => Mage::registry('usePendingFilter') ? 'pending' : 'index')
            ),
            'confirm' => Mage::helper('Mage_Review_Helper_Data')->__('Are you sure?')
        ));

        $statuses = $helper->getReviewStatusesOptionArray();
        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('update_status', array(
            'label'         => $helper->__('Update Status'),
            'url'           => $this->getUrl(
                '*/*/massUpdateStatus',
                array('ret' => Mage::registry('usePendingFilter') ? 'pending' : 'index')
            ),
            'additional'    => array(
                'status'    => array(
                    'name'      => 'status',
                    'type'      => 'select',
                    'class'     => 'required-entry',
                    'label'     => $helper->__('Status'),
                    'values'    => $statuses
                )
            )
        ));
    }

    /**
     * Get row url
     *
     * @param Mage_Review_Model_Review|Varien_Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/catalog_product_review/edit', array(
            'id' => $row->getReviewId(),
            'productId' => $this->getProductId(),
            'customerId' => $this->getCustomerId(),
            'ret'       => ( Mage::registry('usePendingFilter') ) ? 'pending' : null,
        ));
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        if( $this->getProductId() || $this->getCustomerId() ) {
            return $this->getUrl(
                '*/catalog_product_review/' . (Mage::registry('usePendingFilter') ? 'pending' : ''),
                array(
                    'productId' => $this->getProductId(),
                    'customerId' => $this->getCustomerId(),
                )
            );
        } else {
            return $this->getCurrentUrl();
        }
    }
}
