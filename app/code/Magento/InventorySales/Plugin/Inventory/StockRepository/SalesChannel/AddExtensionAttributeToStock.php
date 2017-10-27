<?php
/**
 * Created by PhpStorm.
 * User: hamplr
 * Date: 27.10.17
 * Time: 11:47
 */

namespace Magento\InventorySales\Plugin\Inventory\StockRepository\SalesChannel;

use Magento\Inventory\Model\Stock;
use Magento\InventorySales\Model\GetSalesChannelsByStockInterface;

/**
 * Class AddExtensionAttributeToStock
 * @package Magento\InventorySales\Plugin\Inventory\StockRepository
 */
class AddExtensionAttributeToStock
{

    /** @var GetSalesChannelsByStockInterface  */
    private $getSalesChannelByStock;

    /**
     * AddExtensionAttributeToStock constructor.
     *
     * @param GetSalesChannelsByStockInterface $getSalesChannelByStock
     */
    public function __construct(
        GetSalesChannelsByStockInterface $getSalesChannelByStock
    )
    {
        $this->getSalesChannelByStock = $getSalesChannelByStock;
    }

    /**
     * Get a stock object and enrich the extension attributes with founded sales channels
     *
     * @param Stock $item
     * @return Stock
     */
    public function addAttributeToStock(Stock $item)
    {
        $extensionAttributes = $item->getExtensionAttributes();
        $salesChannelSearchResults = $this->getSalesChannelByStock->get($item->getStockId());
        $extensionAttributes->setSalesChannels($salesChannelSearchResults);
        $item->setExtensionAttributes($extensionAttributes);

        return $item;
    }
}
