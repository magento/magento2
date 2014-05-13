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
 * Sign up for an alert when the product price changes grid
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Extended;

class Stock extends Extended
{
    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * @var \Magento\ProductAlert\Model\StockFactory
     */
    protected $_stockFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\ProductAlert\Model\StockFactory $stockFactory
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\ProductAlert\Model\StockFactory $stockFactory,
        \Magento\Catalog\Helper\Data $catalogData,
        array $data = array()
    ) {
        $this->_stockFactory = $stockFactory;
        $this->_catalogData = $catalogData;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('alertStock');
        $this->setDefaultSort('add_date');
        $this->setDefaultSort('DESC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(false);
        $this->setEmptyText(__('There are no customers for this alert.'));
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $productId = $this->getRequest()->getParam('id');
        $websiteId = 0;
        if ($store = $this->getRequest()->getParam('store')) {
            $websiteId = $this->_storeManager->getStore($store)->getWebsiteId();
        }
        if ($this->_catalogData->isModuleEnabled('Magento_ProductAlert')) {
            $collection = $this->_stockFactory->create()->getCustomerCollection()->join($productId, $websiteId);
            $this->setCollection($collection);
        }
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('firstname', array('header' => __('First Name'), 'index' => 'firstname'));

        $this->addColumn('lastname', array('header' => __('Last Name'), 'index' => 'lastname'));

        $this->addColumn('email', array('header' => __('Email'), 'index' => 'email'));

        $this->addColumn('add_date', array('header' => __('Subscribe Date'), 'index' => 'add_date', 'type' => 'date'));

        $this->addColumn(
            'send_date',
            array('header' => __('Last Notified'), 'index' => 'send_date', 'type' => 'date')
        );

        $this->addColumn('send_count', array('header' => __('Send Count'), 'index' => 'send_count'));

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        $productId = $this->getRequest()->getParam('id');
        $storeId = $this->getRequest()->getParam('store', 0);
        if ($storeId) {
            $storeId = $this->_storeManager->getStore($storeId)->getId();
        }
        return $this->getUrl('catalog/product/alertsStockGrid', array('id' => $productId, 'store' => $storeId));
    }
}
