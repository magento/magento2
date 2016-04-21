<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Shopcart\Customer;

/**
 * Adminhtml items in carts report grid block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Grid extends \Magento\Reports\Block\Adminhtml\Grid\Shopcart
{
    /**
     * @var \Magento\Reports\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $_customersFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Reports\Model\ResourceModel\Customer\CollectionFactory $customersFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Reports\Model\ResourceModel\Customer\CollectionFactory $customersFactory,
        array $data = []
    ) {
        $this->_customersFactory = $customersFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('grid');
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareCollection()
    {
        //TODO: add full name logic
        $collection = $this->_customersFactory->create()->addAttributeToSelect(
            'firstname'
        )->addAttributeToSelect(
            'lastname'
        );

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid|void
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->addCartInfo();
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            ['header' => __('ID'), 'width' => '50px', 'align' => 'right', 'index' => 'entity_id']
        );

        $this->addColumn('firstname', ['header' => __('First Name'), 'index' => 'firstname']);

        $this->addColumn('lastname', ['header' => __('Last Name'), 'index' => 'lastname']);

        $this->addColumn(
            'items',
            [
                'header' => __('Items in Cart'),
                'width' => '70px',
                'sortable' => false,
                'align' => 'right',
                'index' => 'items'
            ]
        );

        $currencyCode = $this->getCurrentCurrencyCode();

        $this->addColumn(
            'total',
            [
                'header' => __('Total'),
                'width' => '70px',
                'sortable' => false,
                'type' => 'currency',
                'align' => 'right',
                'currency_code' => $currencyCode,
                'index' => 'total',
                'renderer' => 'Magento\Reports\Block\Adminhtml\Grid\Column\Renderer\Currency',
                'rate' => $this->getRate($currencyCode)
            ]
        );

        $this->setFilterVisibility(false);

        $this->addExportType('*/*/exportCustomerCsv', __('CSV'));
        $this->addExportType('*/*/exportCustomerExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }
}
