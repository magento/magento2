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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml reviews grid
 *
 * @method int getProductId() getProductId()
 * @method \Magento\Review\Block\Adminhtml\Grid setProductId() setProductId(int $productId)
 * @method int getCustomerId() getCustomerId()
 * @method \Magento\Review\Block\Adminhtml\Grid setCustomerId() setCustomerId(int $customerId)
 * @method \Magento\Review\Block\Adminhtml\Grid setMassactionIdFieldOnlyIndexValue() setMassactionIdFieldOnlyIndexValue(bool $onlyIndex)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Review\Block\Adminhtml;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Review action pager
     *
     * @var \Magento\Review\Helper\Action\Pager
     */
    protected $_reviewActionPager = null;

    /**
     * Review data
     *
     * @var \Magento\Review\Helper\Data
     */
    protected $_reviewData = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Review collection model factory
     *
     * @var \Magento\Review\Model\Resource\Review\Product\CollectionFactory
     */
    protected $_productsFactory;

    /**
     * Review model factory
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\Resource\Review\Product\CollectionFactory $productsFactory
     * @param \Magento\Review\Helper\Data $reviewData
     * @param \Magento\Review\Helper\Action\Pager $reviewActionPager
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\Resource\Review\Product\CollectionFactory $productsFactory,
        \Magento\Review\Helper\Data $reviewData,
        \Magento\Review\Helper\Action\Pager $reviewActionPager,
        \Magento\Framework\Registry $coreRegistry,
        array $data = array()
    ) {
        $this->_productsFactory = $productsFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_reviewData = $reviewData;
        $this->_reviewActionPager = $reviewActionPager;
        $this->_reviewFactory = $reviewFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Initialize grid
     *
     * @return void
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
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _afterLoadCollection()
    {
        /** @var $actionPager \Magento\Review\Helper\Action\Pager */
        $actionPager = $this->_reviewActionPager;
        $actionPager->setStorageId('reviews');
        $actionPager->setItems($this->getCollection()->getResultingIds());

        return parent::_afterLoadCollection();
    }

    /**
     * Prepare collection
     *
     * @return \Magento\Review\Block\Adminhtml\Grid
     */
    protected function _prepareCollection()
    {
        /** @var $model \Magento\Review\Model\Review */
        $model = $this->_reviewFactory->create();
        /** @var $collection \Magento\Review\Model\Resource\Review\Product\Collection */
        $collection = $this->_productsFactory->create();

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
            if (!$customerId) {
                $customerId = $this->getRequest()->getParam('customerId');
            }
            $this->setCustomerId($customerId);
            $collection->addCustomerFilter($this->getCustomerId());
        }

        if ($this->_coreRegistry->registry('usePendingFilter') === true) {
            $collection->addStatusFilter($model->getPendingStatus());
        }

        $collection->addStoreData();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'review_id',
            array(
                'header' => __('ID'),
                'filter_index' => 'rt.review_id',
                'index' => 'review_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            )
        );

        $this->addColumn(
            'created_at',
            array(
                'header' => __('Created'),
                'type' => 'datetime',
                'filter_index' => 'rt.created_at',
                'index' => 'review_created_at',
                'header_css_class' => 'col-date',
                'column_css_class' => 'col-date'
            )
        );

        if (!$this->_coreRegistry->registry('usePendingFilter')) {
            $this->addColumn(
                'status',
                array(
                    'header' => __('Status'),
                    'type' => 'options',
                    'options' => $this->_reviewData->getReviewStatuses(),
                    'filter_index' => 'rt.status_id',
                    'index' => 'status_id'
                )
            );
        }

        $this->addColumn(
            'title',
            array(
                'header' => __('Title'),
                'filter_index' => 'rdt.title',
                'index' => 'title',
                'type' => 'text',
                'truncate' => 50,
                'escape' => true
            )
        );

        $this->addColumn(
            'nickname',
            array(
                'header' => __('Nickname'),
                'filter_index' => 'rdt.nickname',
                'index' => 'nickname',
                'type' => 'text',
                'truncate' => 50,
                'escape' => true,
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            )
        );

        $this->addColumn(
            'detail',
            array(
                'header' => __('Review'),
                'index' => 'detail',
                'filter_index' => 'rdt.detail',
                'type' => 'text',
                'truncate' => 50,
                'nl2br' => true,
                'escape' => true
            )
        );

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'visible_in',
                array('header' => __('Visibility'), 'index' => 'stores', 'type' => 'store', 'store_view' => true)
            );
        }

        $this->addColumn(
            'type',
            array(
                'header' => __('Type'),
                'type' => 'select',
                'index' => 'type',
                'filter' => 'Magento\Review\Block\Adminhtml\Grid\Filter\Type',
                'renderer' => 'Magento\Review\Block\Adminhtml\Grid\Renderer\Type'
            )
        );

        $this->addColumn(
            'name',
            array('header' => __('Product'), 'type' => 'text', 'index' => 'name', 'escape' => true)
        );

        $this->addColumn(
            'sku',
            array(
                'header' => __('SKU'),
                'type' => 'text',
                'index' => 'sku',
                'escape' => true
            )
        );

        $this->addColumn(
            'action',
            array(
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getReviewId',
                'actions' => array(
                    array(
                        'caption' => __('Edit'),
                        'url' => array(
                            'base' => 'review/product/edit',
                            'params' => array(
                                'productId' => $this->getProductId(),
                                'customerId' => $this->getCustomerId(),
                                'ret' => $this->_coreRegistry->registry('usePendingFilter') ? 'pending' : null
                            )
                        ),
                        'field' => 'id'
                    )
                ),
                'filter' => false,
                'sortable' => false
            )
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * Prepare grid mass actions
     *
     * @return void
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('review_id');
        $this->setMassactionIdFilter('rt.review_id');
        $this->setMassactionIdFieldOnlyIndexValue(true);
        $this->getMassactionBlock()->setFormFieldName('reviews');

        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label' => __('Delete'),
                'url' => $this->getUrl(
                    '*/*/massDelete',
                    array('ret' => $this->_coreRegistry->registry('usePendingFilter') ? 'pending' : 'index')
                ),
                'confirm' => __('Are you sure?')
            )
        );

        $statuses = $this->_reviewData->getReviewStatusesOptionArray();
        array_unshift($statuses, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem(
            'update_status',
            array(
                'label' => __('Update Status'),
                'url' => $this->getUrl(
                    '*/*/massUpdateStatus',
                    array('ret' => $this->_coreRegistry->registry('usePendingFilter') ? 'pending' : 'index')
                ),
                'additional' => array(
                    'status' => array(
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses
                    )
                )
            )
        );
    }

    /**
     * Get row url
     *
     * @param \Magento\Review\Model\Review|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'review/product/edit',
            array(
                'id' => $row->getReviewId(),
                'productId' => $this->getProductId(),
                'customerId' => $this->getCustomerId(),
                'ret' => $this->_coreRegistry->registry('usePendingFilter') ? 'pending' : null
            )
        );
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        if ($this->getProductId() || $this->getCustomerId()) {
            return $this->getUrl(
                'review/product' . ($this->_coreRegistry->registry('usePendingFilter') ? 'pending' : ''),
                array('productId' => $this->getProductId(), 'customerId' => $this->getCustomerId())
            );
        } else {
            return $this->getCurrentUrl();
        }
    }
}
