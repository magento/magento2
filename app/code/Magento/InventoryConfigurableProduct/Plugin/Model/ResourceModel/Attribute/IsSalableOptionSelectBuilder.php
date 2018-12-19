<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Plugin\Model\ResourceModel\Attribute;

use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionSelectBuilderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Plugin for OptionSelectBuilderInterface to add "is_salable" filter.
 */
class IsSalableOptionSelectBuilder
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var StockIndexTableNameResolverInterface
     */
    private $stockIndexTableNameResolver;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @param StoreManagerInterface $storeManager
     * @param StockResolverInterface $stockResolver
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        StockResolverInterface $stockResolver,
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        DefaultStockProviderInterface $defaultStockProvider = null
    ) {
        $this->storeManager = $storeManager;
        $this->stockResolver = $stockResolver;
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->defaultStockProvider = $defaultStockProvider ?: ObjectManager::getInstance()
            ->get(DefaultStockProviderInterface::class);
    }

    /**
     * Add "is_salable" filter to select.
     *
     * @param OptionSelectBuilderInterface $subject
     * @param Select $select
     * @return Select
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSelect(
        OptionSelectBuilderInterface $subject,
        Select $select
    ) {
        $websiteCode = $this->storeManager->getWebsite()->getCode();
        $stock = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
        $stockId = (int)$stock->getStockId();
        if ($stockId === $this->defaultStockProvider->getId()) {
            return $select;
        }
        $stockTable = $this->stockIndexTableNameResolver->execute($stockId);

        $select->joinInner(
            ['stock' => $stockTable],
            'stock.sku = entity.sku',
            []
        )->where(
            'stock.is_salable = ?',
            1
        );

        return $select;
    }
}
