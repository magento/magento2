<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml dashboard orders diagram
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backend\Block\Dashboard\Tab;

/**
 * Class \Magento\Backend\Block\Dashboard\Tab\Orders
 *
 * @since 2.0.0
 */
class Orders extends \Magento\Backend\Block\Dashboard\Graph
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param \Magento\Backend\Helper\Dashboard\Data $dashboardData
     * @param \Magento\Backend\Helper\Dashboard\Order $dataHelper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Magento\Backend\Helper\Dashboard\Data $dashboardData,
        \Magento\Backend\Helper\Dashboard\Order $dataHelper,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        parent::__construct($context, $collectionFactory, $dashboardData, $data);
    }

    /**
     * Initialize object
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->setHtmlId('orders');
        parent::_construct();
    }

    /**
     * Prepare chart data
     *
     * @return void
     * @since 2.0.0
     */
    protected function _prepareData()
    {
        $this->getDataHelper()->setParam('store', $this->getRequest()->getParam('store'));
        $this->getDataHelper()->setParam('website', $this->getRequest()->getParam('website'));
        $this->getDataHelper()->setParam('group', $this->getRequest()->getParam('group'));

        $this->setDataRows('quantity');
        $this->_axisMaps = ['x' => 'range', 'y' => 'quantity'];

        parent::_prepareData();
    }
}
