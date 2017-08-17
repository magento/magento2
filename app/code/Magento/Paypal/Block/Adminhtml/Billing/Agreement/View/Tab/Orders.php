<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Paypal\Block\Adminhtml\Billing\Agreement\View\Tab;

use Magento\Paypal\Model\ResourceModel\Billing\Agreement as BillingAgreementResource;
use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory;
use Magento\Backend\Block\Widget\Grid\Extended as ExtendedGrid;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\Registry;

/**
 * Adminhtml billing agreement related orders tab
 * @api
 */
class Orders extends ExtendedGrid implements TabInterface
{
    /**
     * @var  CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var BillingAgreementResource
     */
    protected $billingAgreementResource;

    /**
     * @param TemplateContext $context
     * @param BackendHelper $backendHelper
     * @param CollectionFactory $collectionFactory
     * @param Registry $coreRegistry
     * @param BillingAgreementResource $billingAgreementResource
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        BackendHelper $backendHelper,
        CollectionFactory $collectionFactory,
        Registry $coreRegistry,
        BillingAgreementResource $billingAgreementResource,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->collectionFactory = $collectionFactory;
        $this->billingAgreementResource = $billingAgreementResource;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Related Orders');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Related Orders');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('billing_agreement_orders');
        $this->setUseAjax(true);
    }

    /**
     * Get grid url
     *
     * @return string
     * @since 100.1.0
     */
    public function getGridUrl()
    {
        return $this->getUrl('paypal/billing_agreement/ordersGrid', ['_current' => true]);
    }

    /**
     * Apply various selection filters to prepare the sales order grid collection.
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $billingAgreement = $this->coreRegistry->registry('current_billing_agreement');
        if ($billingAgreement) {
            $collection = $this->collectionFactory->getReport('sales_order_grid_data_source')->addFieldToSelect(
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
            );
            $this->billingAgreementResource->addOrdersFilter($collection, $billingAgreement->getId());
            $this->setCollection($collection);
        }
        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
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
        return parent::_prepareColumns();
    }
}
