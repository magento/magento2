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
namespace Magento\Catalog\Model\Rss\Product;

/**
 * Class NotifyStock
 * @package Magento\Catalog\Model\Rss\Product
 */
class NotifyStock extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\CatalogInventory\Model\Resource\StockFactory
     */
    protected $stockFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $productStatus;

    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\CatalogInventory\Model\Resource\StockFactory $stockFactory
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\CatalogInventory\Model\Resource\StockFactory $stockFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->productFactory = $productFactory;
        $this->stockFactory = $stockFactory;
        $this->productStatus = $productStatus;
        $this->eventManager = $eventManager;
    }


    /**
     * @return \Magento\Catalog\Model\Resource\Product\Collection
     */
    public function getProductsCollection()
    {
        /* @var $product \Magento\Catalog\Model\Product */
        $product = $this->productFactory->create();
        /* @var $collection \Magento\Catalog\Model\Resource\Product\Collection */
        $collection = $product->getCollection();
        /** @var $resourceStock \Magento\CatalogInventory\Model\Resource\Stock */
        $resourceStock = $this->stockFactory->create();
        $resourceStock->addLowStockFilter(
            $collection,
            array('qty', 'notify_stock_qty', 'low_stock_date', 'use_config' => 'use_config_notify_stock_qty')
        );
        $collection->addAttributeToSelect('name', true)
            ->addAttributeToFilter('status', array('in' => $this->productStatus->getVisibleStatusIds()))
            ->setOrder('low_stock_date');

        $this->eventManager->dispatch(
            'rss_catalog_notify_stock_collection_select',
            array('collection' => $collection)
        );
        return $collection;
    }
}
