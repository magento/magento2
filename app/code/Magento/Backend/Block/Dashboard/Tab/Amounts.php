<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Dashboard\Tab;

/**
 * Adminhtml dashboard order amounts diagram
 * @deprecated dashboard graphs were migrated to dynamic chart.js solution
 * @see dashboard.chart.amounts in adminhtml_dashboard_index.xml
 */
class Amounts extends \Magento\Backend\Block\Dashboard\Graph
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param \Magento\Backend\Helper\Dashboard\Data $dashboardData
     * @param \Magento\Backend\Helper\Dashboard\Order $dataHelper
     * @param array $data
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
     */
    protected function _construct()
    {
        $this->setHtmlId('amounts');
        parent::_construct();
    }

    /**
     * Prepare chart data
     *
     * @return void
     */
    protected function _prepareData()
    {
        $this->getDataHelper()->setParam('store', $this->getRequest()->getParam('store'));
        $this->getDataHelper()->setParam('website', $this->getRequest()->getParam('website'));
        $this->getDataHelper()->setParam('group', $this->getRequest()->getParam('group'));

        $this->setDataRows('revenue');
        $this->_axisMaps = ['x' => 'range', 'y' => 'revenue'];

        parent::_prepareData();
    }
}
