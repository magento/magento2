<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventoryGraphQl\Model\Resolver;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * {@inheritdoc}
 */
class OnlyXLeftInStockResolver implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @param ValueFactory $valueFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        ValueFactory $valueFactory,
        ScopeConfigInterface $scopeConfig,
        StockRegistryInterface $stockRegistry
    ) {
        $this->valueFactory = $valueFactory;
        $this->scopeConfig = $scopeConfig;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): Value
    {
        if (!array_key_exists('model', $value) || !$value['model'] instanceof ProductInterface) {
            $result = function () {
                return null;
            };

            return $this->valueFactory->create($result);
        }

        /* @var $product ProductInterface */
        $product = $value['model'];
        $onlyXLeftQty = $this->getOnlyXLeftQty($product);

        $result = function () use ($onlyXLeftQty) {
            return $onlyXLeftQty;
        };

        return $this->valueFactory->create($result);
    }

    /**
     * Get product qty left when "Catalog > Inventory > Stock Options > Only X left Threshold" is greater than 0
     *
     * @param ProductInterface $product
     *
     * @return null|float
     */
    private function getOnlyXLeftQty(ProductInterface $product): ?float
    {
        $thresholdQty = (float)$this->scopeConfig->getValue(
            Configuration::XML_PATH_STOCK_THRESHOLD_QTY,
            ScopeInterface::SCOPE_STORE
        );
        if($thresholdQty === 0){
            return null;
        }

        $stockItem = $this->stockRegistry->getStockItem($product->getId());

        $stockCurrentQty = $this->stockRegistry->getStockStatus(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        )->getQty();

        $stockLeft = $stockCurrentQty - $stockItem->getMinQty();

        $thresholdQty = (float)$this->scopeConfig->getValue(
            Configuration::XML_PATH_STOCK_THRESHOLD_QTY,
            ScopeInterface::SCOPE_STORE
        );

        if ($stockCurrentQty > 0 && $stockLeft <= $thresholdQty) {
            return $stockLeft;
        }

        return null;
    }
}
