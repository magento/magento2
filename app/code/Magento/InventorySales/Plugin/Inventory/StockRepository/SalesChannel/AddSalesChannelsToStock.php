<?php
/**
 * Created by PhpStorm.
 * User: hamplr
 * Date: 27.10.17
 * Time: 11:47
 */
namespace Magento\InventorySales\Plugin\Inventory\StockRepository\SalesChannel;

use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventorySales\Model\GetAssignedSalesChannelsForStockInterface;

/**
 * TODO: dockblock
 */
class AddSalesChannelsToStock
{
    /**
     * @var GetAssignedSalesChannelsForStockInterface
     */
    private $getAssignedSalesChannelsForStock;

    /**
     * @param GetAssignedSalesChannelsForStockInterface $getAssignedSalesChannelsForStock
     */
    public function __construct(
        GetAssignedSalesChannelsForStockInterface $getAssignedSalesChannelsForStock
    ) {
        $this->getAssignedSalesChannelsForStock = $getAssignedSalesChannelsForStock;
    }

    /**
     * Get a stock object and enrich the extension attributes with founded sales channels
     *
     * @param StockInterface $stock
     * @return StockInterface
     */
    public function addAttributeToStock(StockInterface $stock): StockInterface
    {
        $salesChannels = $this->getAssignedSalesChannelsForStock->execute($stock->getStockId());

        $extensionAttributes = $stock->getExtensionAttributes();
        $extensionAttributes->setSalesChannels($salesChannels);
        $stock->setExtensionAttributes($extensionAttributes);
        return $stock;
    }
}
