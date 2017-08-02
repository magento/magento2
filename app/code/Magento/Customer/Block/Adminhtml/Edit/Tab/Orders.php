<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;

/**
 * Adminhtml customer orders grid block
 *
 * @api
 * @since 2.0.0
 */
class Orders extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Sales reorder
     *
     * @var \Magento\Sales\Helper\Reorder
     * @since 2.0.0
     */
    protected $_salesReorder = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @var  \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory
     * @since 2.0.0
     */
    protected $collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory
     * @param \Magento\Sales\Helper\Reorder $salesReorder
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory,
        \Magento\Sales\Helper\Reorder $salesReorder,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_salesReorder = $salesReorder;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customer_orders_grid');
        $this->setDefaultSort('created_at', 'desc');
        $this->setUseAjax(true);
    }

    /**
     * Apply various selection filters to prepare the sales order grid collection.
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->getReport('sales_order_grid_data_source')->addFieldToSelect(
            'entity_id'
        )->addFieldToSelect(
            'increment_id'
        )->addFieldToSelect(
            'customer_id'
        )->addFieldToSelect(
            'created_at'
        )->addFieldToSelect(
            'grand_total'
        )->addFieldToSelect(
            'order_currency_code'
        )->addFieldToSelect(
            'store_id'
        )->addFieldToSelect(
            'billing_name'
        )->addFieldToSelect(
            'shipping_name'
        )->addFieldToFilter(
            'customer_id',
            $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)
        );

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', ['header' => __('Order'), 'width' => '100', 'index' => 'increment_id']);

        $this->addColumn(
            'created_at',
            ['header' => __('Purchased'), 'index' => 'created_at', 'type' => 'datetime']
        );

        $this->addColumn('billing_name', ['header' => __('Bill-to Name'), 'index' => 'billing_name']);

        $this->addColumn('shipping_name', ['header' => __('Ship-to Name'), 'index' => 'shipping_name']);

        $this->addColumn(
            'grand_total',
            [
                'header' => __('Order Total'),
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

        if ($this->_salesReorder->isAllow()) {
            $this->addColumn(
                'action',
                [
                    'header' => ' ',
                    'filter' => false,
                    'sortable' => false,
                    'width' => '100px',
                    'renderer' => \Magento\Sales\Block\Adminhtml\Reorder\Renderer\Action::class
                ]
            );
        }

        return parent::_prepareColumns();
    }

    /**
     * Retrieve the Url for a specified sales order row.
     *
     * @param \Magento\Sales\Model\Order|\Magento\Framework\DataObject $row
     * @return string
     * @since 2.0.0
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $row->getId()]);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getGridUrl()
    {
        return $this->getUrl('customer/*/orders', ['_current' => true]);
    }
}
