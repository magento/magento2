<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\InventoryApi\StockResolver;

use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;

class ProcessCheckAdminPartPlugin
{
    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProviderInterface;

    /**
     * ProcessCheckAdminPartPlugin constructor.
     *
     * @param DefaultStockProviderInterface $defaultStockProviderInterface
     * @param StockRepositoryInterface      $stockRepositoryInterface
     */
    public function __construct(
        DefaultStockProviderInterface $defaultStockProviderInterface,
        StockRepositoryInterface $stockRepositoryInterface
    ) {
        $this->defaultStockProviderInterface = $defaultStockProviderInterface;
        $this->stockRepository = $stockRepositoryInterface;
    }

    /**
     * @param StockResolverInterface $stockResolverInterface
     * @param callable               $proceed
     * @param string                 $type
     * @param string                 $code
     *
     * @return StockInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundGet(
        StockResolverInterface $stockResolverInterface,
        callable $proceed,
        string $type,
        string $code
    ) {
        if ($code == WebsiteInterface::ADMIN_CODE) {
            return $this->stockRepository->get($this->defaultStockProviderInterface->getId());
        }

        return $proceed($type, $code);
    }
}