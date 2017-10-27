<?php
/**
 * Created by PhpStorm.
 * User: tschampelb
 * Date: 26.10.17
 * Time: 12:08
 */

namespace Magento\InventorySales\Model;


use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Inventory\Model\StockFactory;

class StockResolver implements StockResolverInterface
{

    /** @var  \Magento\InventorySales\Model\ResourceModel\StockResolver */
    private $stockResolver;

    /**
     * @var StockFactory
     */
    private $stockFactory;

    /**
     * StockResolver constructor.
     * @param StockFactory $stockFactory
     * @param StockResolver $stockResolver
     */
    public function __construct(
        StockFactory $stockFactory,
        StockResolver $stockResolver)
    {
        $this->stockFactory = $stockFactory;
        $this->stockResolver = $stockResolver;
    }

    /**
     * Get Stock Object by given type and code.
     *
     * @param string $type
     * @param string $code
     * @return StockInterface
     */
    public function get(string $type, string $code): StockInterface
    {
        $stockId = $this->stockResolver->resolve($type, $code);
        $stockModel = $this->stockFactory->create()->getItemById($stockId);
        return $stockModel;
    }
}