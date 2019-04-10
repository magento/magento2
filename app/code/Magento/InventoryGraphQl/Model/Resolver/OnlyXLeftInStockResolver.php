<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryGraphQl\Model\Resolver;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;

/**
 * @inheritdoc
 */
class OnlyXLeftInStockResolver implements ResolverInterface
{
    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $getStockIdForCurrentWebsite;

    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     */
    public function __construct(
        GetProductSalableQtyInterface $getProductSalableQty,
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        GetStockItemConfigurationInterface $getStockItemConfiguration
    ) {
        $this->getProductSalableQty = $getProductSalableQty;
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        /* @var $product ProductInterface */
        $product = $value['model'];
        $onlyXLeftQty = $this->getOnlyXLeftQty($product->getSku());

        return $onlyXLeftQty;
    }

    /**
     * Get quantity of a specified product when equals or lower then configured threshold.
     *
     * @param string $sku
     * @return null|float
     * @throws SkuIsNotAssignedToStockException
     * @throws LocalizedException
     */
    private function getOnlyXLeftQty(string $sku): ?float
    {
        $stockId = $this->getStockIdForCurrentWebsite->execute();
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);

        $thresholdQty = $stockItemConfiguration->getStockThresholdQty();
        if ($thresholdQty === 0) {
            return null;
        }

        try {
            $productSalableQty = $this->getProductSalableQty->execute($sku, $stockId);
            $stockLeft = $productSalableQty - $stockItemConfiguration->getMinQty();

            if ($productSalableQty > 0 && $stockLeft <= $thresholdQty) {
                return (float)$stockLeft;
            }
        } catch (InputException | LocalizedException $e) {
            // this is expected behavior because ex. Group product doesn't have own quantity
            return null;
        }

        return null;
    }
}
