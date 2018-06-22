<?php
/**
 * Company: Ping24/7 GmbH
 * Developer: Stepan Furman (s.furman@ping247.de)
 * Date: 22.06.18
 * Time: 14:23
 */

namespace Magento\InventoryCatalog\Plugin\Catalog;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

class ProductPlugin
{
    /**
     * @var IsProductSalableInterface
     */
    private $isProductSalable;
    /**
     * @var StockResolverInterface
     */
    private $stockResolver;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param IsProductSalableInterface $isProductSalable
     * @param StockResolverInterface $stockResolver
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        IsProductSalableInterface $isProductSalable,
        StockResolverInterface $stockResolver,
        StoreManagerInterface $storeManager
    ) {
        $this->isProductSalable = $isProductSalable;
        $this->stockResolver = $stockResolver;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Product $subject
     * @param callable $proceed
     * @return boolean
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function aroundIsSalable(Product $subject, callable $proceed)
    {
        $website = $this->storeManager->getWebsite();
        $stock = $this->stockResolver->get(SalesChannelInterface::TYPE_WEBSITE, $website->getCode());

        return $this->isProductSalable->execute($subject->getSku(), $stock->getStockId());
    }
}
