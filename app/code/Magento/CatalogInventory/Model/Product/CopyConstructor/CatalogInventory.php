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
namespace Magento\CatalogInventory\Model\Product\CopyConstructor;

class CatalogInventory implements \Magento\Catalog\Model\Product\CopyConstructorInterface
{
    /**
     * @var \Magento\CatalogInventory\Service\V1\StockItemService
     */
    protected $stockItemService;

    /**
     * @param \Magento\CatalogInventory\Service\V1\StockItemService $stockItemService
     */
    public function __construct(
        \Magento\CatalogInventory\Service\V1\StockItemService $stockItemService
    ) {
        $this->stockItemService = $stockItemService;
    }

    /**
     * Copy product inventory data (used for product duplicate functionality)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Product $duplicate
     * @return void
     */
    public function build(\Magento\Catalog\Model\Product $product, \Magento\Catalog\Model\Product $duplicate)
    {
        $stockData = [
            'use_config_min_qty' => 1,
            'use_config_min_sale_qty' => 1,
            'use_config_max_sale_qty' => 1,
            'use_config_backorders' => 1,
            'use_config_notify_stock_qty' => 1
        ];
        /** @var \Magento\CatalogInventory\Service\V1\Data\StockItem $currentStockItemDo */
        $currentStockItemDo = $this->stockItemService->getStockItem($product->getId());
        if ($currentStockItemDo->getStockId()) {
            $stockData += [
                'use_config_enable_qty_inc' => $currentStockItemDo->isUseConfigEnableQtyInc(),
                'enable_qty_increments' => $currentStockItemDo->isEnableQtyIncrements(),
                'use_config_qty_increments' => $currentStockItemDo->isUseConfigQtyIncrements(),
                'qty_increments' => $currentStockItemDo->getQtyIncrements(),
            ];
        }
        $duplicate->setStockData($stockData);
    }
}
