<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryConfiguration\Block\Adminhtml\Product\Lowstock;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid as GridWidget;
use Magento\Backend\Helper\Data;
use Magento\Framework\Data\Collection as DataCollection;
use Magento\InventoryConfiguration\Model\ResourceModel\Product\Lowstock\CollectionFactory;

/**
 * Adminhtml low stock products report grid block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Grid extends GridWidget
{
    /**
     * @var CollectionFactory
     */
    protected $lowstockCollectionFactory;

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
        $this->lowstockCollectionFactory = $lowstockCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return GridWidget
     */
    protected function _prepareCollection()
    {
        /** @var $collection \Magento\InventoryConfiguration\Model\ResourceModel\Product\Lowstock\Collection  */
        $collection = $this->lowstockCollectionFactory->create();
        $collection->addFieldToSelect(
            '*'
        )
        ->joinInventoryConfiguration()
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
