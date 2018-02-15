<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowStockNotification\Block\Adminhtml\Product\Lowstock;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid as GridWidget;
use Magento\Backend\Helper\Data;
use Magento\Framework\Data\Collection as DataCollection;
use Magento\InventoryLowStockNotification\Model\ResourceModel\Product\Lowstock\Collection as LowstockCollection;
use Magento\InventoryLowStockNotification\Model\ResourceModel\Product\Lowstock\CollectionFactory;

/**
 *  Low stock products report grid block
 *  @api
 */
class Grid extends GridWidget
{
    /**
     * @var CollectionFactory
     */
    private $lowstockCollectionFactory;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param CollectionFactory $lowstockCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $lowstockCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->lowstockCollectionFactory = $lowstockCollectionFactory;
    }

    /**
     * @return GridWidget
     */
    protected function _prepareCollection(): GridWidget
    {
        /** @var $collection LowstockCollection  */
        $collection = $this->lowstockCollectionFactory->create();
        $collection->addFieldToSelect(
            '*'
        )
        ->joinCatalogProduct()
        ->filterByIsQtyProductTypes()
        ->useNotifyStockQtyFilter()
        ->setOrder(
            'quantity',
            DataCollection::SORT_ORDER_ASC
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
}
