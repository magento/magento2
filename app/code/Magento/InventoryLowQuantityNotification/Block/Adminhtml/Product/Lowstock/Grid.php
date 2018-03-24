<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Block\Adminhtml\Product\Lowstock;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid as GridWidget;
use Magento\Backend\Helper\Data;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\LowQuantityCollection;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\LowQuantityCollectionFactory;

/**
 * Low quantity products report grid block
 *
 * @api
 */
class Grid extends GridWidget
{
    /**
     * @var LowQuantityCollectionFactory
     */
    private $lowQuantityCollectionFactory;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param LowQuantityCollectionFactory $lowQuantityCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        LowQuantityCollectionFactory $lowQuantityCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->lowQuantityCollectionFactory = $lowQuantityCollectionFactory;
    }

    /**
     * @return GridWidget
     */
    protected function _prepareCollection(): GridWidget
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

        /** @var LowQuantityCollection $collection  */
        $collection = $this->lowQuantityCollectionFactory->create();

        if ($storeId) {
            $collection->addStoreFilter($storeId);
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
}
