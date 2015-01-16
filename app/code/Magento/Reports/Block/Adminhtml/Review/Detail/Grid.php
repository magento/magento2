<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Review\Detail;

/**
 * Adminhtml report reviews product grid block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Reports\Model\Resource\Review\CollectionFactory
     */
    protected $_reviewsFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Reports\Model\Resource\Review\CollectionFactory $reviewsFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Reports\Model\Resource\Review\CollectionFactory $reviewsFactory,
        array $data = []
    ) {
        $this->_reviewsFactory = $reviewsFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('reviews_grid');
    }

    /**
     * Apply sorting and filtering to reports review collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_reviewsFactory->create()->addProductFilter((int)$this->getRequest()->getParam('id'));

        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * Initialize grid report review columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('nickname', ['header' => __('Customer'), 'width' => '100px', 'index' => 'nickname']);

        $this->addColumn('title', ['header' => __('Title'), 'width' => '150px', 'index' => 'title']);

        $this->addColumn('detail', ['header' => __('Detail'), 'index' => 'detail']);

        $this->addColumn(
            'created_at',
            ['header' => __('Created'), 'index' => 'created_at', 'width' => '200px', 'type' => 'datetime']
        );

        $this->setFilterVisibility(false);

        $this->addExportType('*/*/exportProductDetailCsv', __('CSV'));
        $this->addExportType('*/*/exportProductDetailExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }
}
