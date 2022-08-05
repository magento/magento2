<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Block\Adminhtml\Product\Lowstock;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Data\Collection as DataCollection;
use Magento\Reports\Model\ResourceModel\Product\Lowstock\Collection;
use Magento\Reports\Model\ResourceModel\Product\Lowstock\CollectionFactory;

/**
 * Adminhtml low stock products report grid block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Grid extends \Magento\Backend\Block\Widget\Grid
{
    /**
     * @var CollectionFactory
     */
    protected $_lowstocksFactory;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param CollectionFactory $lowstocksFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $lowstocksFactory,
        array $data = []
    ) {
        $this->_lowstocksFactory = $lowstocksFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @inheritDoc
     *
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareCollection()
    {
        $website = $this->getRequest()->getParam('website');
        $group = $this->getRequest()->getParam('group');
        $store = $this->getRequest()->getParam('store');

        if ($website) {
            $storeIds = $this->_storeManager->getWebsite($website)->getStoreIds();
            $storeId = array_pop($storeIds);
        } elseif ($group) {
            $storeIds = $this->_storeManager->getGroup($group)->getStoreIds();
            $storeId = array_pop($storeIds);
        } elseif ($store) {
            $storeId = (int)$store;
        } else {
            $storeId = null;
        }

        /** @var $collection Collection  */
        $collection = $this->_lowstocksFactory->create()->addAttributeToSelect(
            '*'
        )->filterByIsQtyProductTypes()->joinInventoryItem(
            'qty'
        )->useManageStockFilter(
            $storeId
        )->useNotifyStockQtyFilter(
            $storeId
        )->setOrder(
            'qty',
            DataCollection::SORT_ORDER_ASC
        )->addAttributeToFilter(
            'status',
            Status::STATUS_ENABLED
        );

        if ($storeId) {
            $collection->addStoreFilter($storeId);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
}
