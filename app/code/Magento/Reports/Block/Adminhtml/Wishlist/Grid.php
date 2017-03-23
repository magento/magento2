<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Wishlist;

/**
 * Adminhtml wishlist report grid block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Reports\Model\ResourceModel\Wishlist\Product\CollectionFactory
     */
    protected $_productsFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Reports\Model\ResourceModel\Wishlist\Product\CollectionFactory $productsFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Reports\Model\ResourceModel\Wishlist\Product\CollectionFactory $productsFactory,
        array $data = []
    ) {
        $this->_productsFactory = $productsFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('wishlistReportGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_productsFactory->create()->addAttributeToSelect(
            'entity_id'
        )->addAttributeToSelect(
            'name'
        )->addWishlistCount();

        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', ['header' => __('ID'), 'width' => '50px', 'index' => 'entity_id']);

        $this->addColumn('name', ['header' => __('Name'), 'index' => 'name']);

        $this->addColumn(
            'wishlists',
            ['header' => __('Wish Lists'), 'width' => '50px', 'align' => 'right', 'index' => 'wishlists']
        );

        $this->addColumn(
            'bought_from_wishlists',
            [
                'header' => __('Wish List Purchase'),
                'width' => '50px',
                'align' => 'right',
                'sortable' => false,
                'index' => 'bought_from_wishlists'
            ]
        );

        $this->addColumn(
            'w_vs_order',
            [
                'header' => __('Wish List vs. Regular Order'),
                'width' => '50px',
                'align' => 'right',
                'sortable' => false,
                'index' => 'w_vs_order'
            ]
        );

        $this->addColumn(
            'num_deleted',
            [
                'header' => __('Times Deleted'),
                'width' => '50px',
                'align' => 'right',
                'sortable' => false,
                'index' => 'num_deleted'
            ]
        );

        $this->addExportType('*/*/exportWishlistCsv', __('CSV'));
        $this->addExportType('*/*/exportWishlistExcel', __('Excel XML'));

        $this->setFilterVisibility(false);

        return parent::_prepareColumns();
    }
}
