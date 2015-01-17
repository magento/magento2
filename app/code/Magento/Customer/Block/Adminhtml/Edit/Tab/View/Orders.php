<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab\View;

use Magento\Customer\Controller\RegistryConstants;

/**
 * Adminhtml customer recent orders grid block
 */
class Orders extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Grid\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Sales\Model\Resource\Order\Grid\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Sales\Model\Resource\Order\Grid\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Initialize the orders grid.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customer_view_orders_grid');
        $this->setDefaultSort('created_at', 'desc');
        $this->setSortable(false);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }

    /**
     * {@inheritdoc}
     */
    protected function _preparePage()
    {
        $this->getCollection()->setPageSize(5)->setCurPage(1);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create()->addFieldToFilter(
            'customer_id',
            $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)
        )->setIsCustomerMode(
            true
        );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'increment_id',
            ['header' => __('Order'), 'align' => 'center', 'index' => 'increment_id', 'width' => '100px']
        );

        $this->addColumn(
            'created_at',
            ['header' => __('Purchase Date'), 'index' => 'created_at', 'type' => 'datetime']
        );

        $this->addColumn('billing_name', ['header' => __('Bill-to Name'), 'index' => 'billing_name']);

        $this->addColumn('shipping_name', ['header' => __('Shipped-to Name'), 'index' => 'shipping_name']);

        $this->addColumn(
            'grand_total',
            [
                'header' => __('Grand Total'),
                'index' => 'grand_total',
                'type' => 'currency',
                'currency' => 'order_currency_code'
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'store_id',
                ['header' => __('Purchase Point'), 'index' => 'store_id', 'type' => 'store', 'store_view' => true]
            );
        }

        $this->addColumn(
            'action',
            [
                'header' => ' ',
                'filter' => false,
                'sortable' => false,
                'width' => '100px',
                'renderer' => 'Magento\Sales\Block\Adminhtml\Reorder\Renderer\Action'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $row->getId()]);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeadersVisibility()
    {
        return $this->getCollection()->getSize() >= 0;
    }
}
