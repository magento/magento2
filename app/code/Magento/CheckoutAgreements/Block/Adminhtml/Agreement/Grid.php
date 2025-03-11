<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Block\Adminhtml\Agreement;

use Magento\Framework\App\ObjectManager;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Grid\CollectionFactory as GridCollectionFactory;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory
     * @deprecated 100.2.2
     */
    protected $_collectionFactory;

    /**
     * @param GridCollectionFactory
     */
    private $gridCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory $collectionFactory
     * @param array $data
     * @param GridCollectionFactory $gridColFactory
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory $collectionFactory,
        array $data = [],
        ?GridCollectionFactory $gridColFactory = null
    ) {

        $this->_collectionFactory = $collectionFactory;
        $this->gridCollectionFactory = $gridColFactory
            ? : ObjectManager::getInstance()->get(GridCollectionFactory::class);

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setDefaultSort('agreement_id');
        $this->setId('agreementGrid');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return $this
     * @codeCoverageIgnore
     */
    protected function _prepareCollection()
    {
        $this->setCollection($this->gridCollectionFactory->create());
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'agreement_id',
            [
                'header' => __('ID'),
                'index' => 'agreement_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Condition'),
                'index' => 'name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'stores',
                [
                    'header' => __('Store View'),
                    'index' => 'stores',
                    'type' => 'store',
                    'store_all' => true,
                    'store_view' => true,
                    'sortable' => false,
                    'filter_condition_callback' => [$this, '_filterStoreCondition'],
                    'header_css_class' => 'col-store-view',
                    'column_css_class' => 'col-store-view'
                ]
            );
        }

        $this->addColumn(
            'is_active',
            [
                'header' => __('Status'),
                'index' => 'is_active',
                'type' => 'options',
                'options' => [0 => __('Disabled'), 1 => __('Enabled')],
                'header_css_class' => 'col-status',
                'column_css_class' => 'col-status'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return void
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    /**
     * @param \Magento\Framework\Data\Collection $collection
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _filterStoreCondition($collection, $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @codeCoverageIgnore
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('checkout/*/edit', ['id' => $row->getId()]);
    }
}
