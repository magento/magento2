<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryProductAlert\Block\Adminhtml\Product\Edit\Tab\Alerts;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Module\Manager;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\ProductAlert\Model\StockFactory;
use Magento\Store\Api\Data\WebsiteInterface;

/**
 * Add Website Id and Stock Name to product Alert Grid.
 */
class Stock extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts\Stock
{
    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param StockFactory $stockFactory
     * @param Manager $moduleManager
     * @param StockResolverInterface $stockResolver
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        StockFactory $stockFactory,
        Manager $moduleManager,
        StockResolverInterface $stockResolver,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $stockFactory, $moduleManager, $data);

        $this->stockResolver = $stockResolver;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn('website_id', ['header' => __('Website'), 'index' => 'website_id']);
        $this->addColumn('stock_name', ['header' => __('Stock'), 'index' => 'stock_name']);
    }

    /**
     * @inheritdoc
     */
    protected function _afterLoadCollection()
    {
        parent::_afterLoadCollection();

        foreach ($this->getCollection()->getItems() as $item) {
            /** @var WebsiteInterface $website */
            $website = $this->_storeManager->getWebsite($item->getWebsiteId());
            $stock = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $website->getCode());
            $item->setStockName($stock->getName());
        }
    }
}
