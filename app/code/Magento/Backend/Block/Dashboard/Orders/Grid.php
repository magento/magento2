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
namespace Magento\Backend\Block\Dashboard\Orders;

/**
 * Adminhtml dashboard recent orders grid
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Grid extends \Magento\Backend\Block\Dashboard\Grid
{
    /**
     * @var \Magento\Reports\Model\Resource\Order\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Reports\Model\Resource\Order\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Reports\Model\Resource\Order\CollectionFactory $collectionFactory,
        array $data = array()
    ) {
        $this->_moduleManager = $moduleManager;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('lastOrdersGrid');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        if (!$this->_moduleManager->isEnabled('Magento_Reports')) {
            return $this;
        }
        $collection = $this->_collectionFactory->create()->addItemCountExpr()->joinCustomerName(
            'customer'
        )->orderByCreatedAt();

        if ($this->getParam('store') || $this->getParam('website') || $this->getParam('group')) {
            if ($this->getParam('store')) {
                $collection->addAttributeToFilter('store_id', $this->getParam('store'));
            } else if ($this->getParam('website')) {
                $storeIds = $this->_storeManager->getWebsite($this->getParam('website'))->getStoreIds();
                $collection->addAttributeToFilter('store_id', array('in' => $storeIds));
            } else if ($this->getParam('group')) {
                $storeIds = $this->_storeManager->getGroup($this->getParam('group'))->getStoreIds();
                $collection->addAttributeToFilter('store_id', array('in' => $storeIds));
            }

            $collection->addRevenueToSelect();
        } else {
            $collection->addRevenueToSelect(true);
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepares page sizes for dashboard grid with las 5 orders
     *
     * @return void
     */
    protected function _preparePage()
    {
        $this->getCollection()->setPageSize($this->getParam($this->getVarNameLimit(), $this->_defaultLimit));
        // Remove count of total orders
        // $this->getCollection()->setCurPage($this->getParam($this->getVarNamePage(), $this->_defaultPage));
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'customer',
            array('header' => __('Customer'), 'sortable' => false, 'index' => 'customer', 'default' => __('Guest'))
        );

        $this->addColumn(
            'items',
            array(
                'header' => __('Items'),
                'align' => 'right',
                'type' => 'number',
                'sortable' => false,
                'index' => 'items_count'
            )
        );

        $baseCurrencyCode = $this->_storeManager->getStore((int)$this->getParam('store'))->getBaseCurrencyCode();

        $this->addColumn(
            'total',
            array(
                'header' => __('Grand Total'),
                'align' => 'right',
                'sortable' => false,
                'type' => 'currency',
                'currency_code' => $baseCurrencyCode,
                'index' => 'revenue'
            )
        );

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('sales/order/view', array('order_id' => $row->getId()));
    }
}
