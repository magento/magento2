<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventorySales\Model\ResourceModel\StockResolver as StockResolverResourceModel;

/**
 * The stock resolver is responsible for getting the linked stock for a certain sales channel.
 */
class StockResolver implements StockResolverInterface
{
    /**
     * @var StockResolverResourceModel
     */
    private $stockResolverResourceModel;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepositoryInterface;

    /**
     * StockResolver constructor.
     *
     * @param StockRepositoryInterface $stockRepositoryInterface
     * @param StockResolverResourceModel $stockResolverResourceModel
     */
    public function __construct(
        StockRepositoryInterface $stockRepositoryInterface,
        StockResolverResourceModel $stockResolverResourceModel)
    {
        $this->stockRepositoryInterface = $stockRepositoryInterface;
        $this->stockResolverResourceModel = $stockResolverResourceModel;
    }

    /**
     * Get Stock Object by given type and code.
     *
     * @param string $type
     * @param string $code
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return StockInterface
     */
    public function get(string $type, string $code): StockInterface
    {
        $stockId = $this->stockResolverResourceModel->resolve($type, $code);
        return $this->stockRepositoryInterface->get($stockId);
    }
}
