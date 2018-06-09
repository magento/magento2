<?php
/**
 * Created by PhpStorm.
 * User: alessandro
 * Date: 09/06/18
 * Time: 15.35
 */
namespace Magento\InventoryFrontendUi\Plugin\Block\Stockqty;

use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;

class AbstractStockqtyPlugin
{
    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private $stockByWebsiteId;

    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    public function __construct(
        StockByWebsiteIdResolverInterface $stockByWebsiteId,
        GetStockItemConfigurationInterface $getStockItemConfig,
        GetProductSalableQtyInterface $getProductSalableQty
    ) {
        $this->getStockItemConfiguration = $getStockItemConfig;
        $this->stockByWebsiteId = $stockByWebsiteId;
        $this->getProductSalableQty = $getProductSalableQty;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsMsgVisible(
        \Magento\CatalogInventory\Block\Stockqty\AbstractStockqty $subject,
        callable $proceed
    ): bool {
        $sku = $subject->getProduct()->getSku();
        $websiteId = $subject->getProduct()->getStore()->getWebsiteId();
        $stockId = $this->stockByWebsiteId->execute($websiteId)->getStockId();
        $stockItemConfig = $this->getStockItemConfiguration->execute($sku, $stockId);
        if (null === $stockItemConfig) {
            return false;
        }
        return $this->getProductSalableQty->execute($sku, $stockId) > 0
            && $this->getStockQtyLeft($sku, $stockId) <= $stockItemConfig->getStockThresholdQty();
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetStockQtyLeft(
        \Magento\CatalogInventory\Block\Stockqty\AbstractStockqty $subject,
        callable $proceed
    ): float {
        $sku = $subject->getProduct()->getSku();
        $websiteId = $subject->getProduct()->getStore()->getWebsiteId();
        $stockId = $this->stockByWebsiteId->execute($websiteId)->getStockId();
        return $this->getStockQtyLeft($sku, $stockId);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getStockQtyLeft(string $sku, int $stockId): float
    {
        $stockItemConfig = $this->getStockItemConfiguration->execute($sku, $stockId);
        if (null === $stockItemConfig) {
            return $this->getProductSalableQty->execute($sku, $stockId);
        }
        $minStockQty = $stockItemConfig->getMinQty();
        return $this->getProductSalableQty->execute($sku, $stockId) - $minStockQty;
    }
}