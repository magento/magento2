<?php
/**
 * Created by PhpStorm.
 * User: roettigl
 * Date: 29.10.17
 * Time: 15:24
 */

namespace Magento\InventoryCatalog\Plugin\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\ReservationInterface;
use Magento\InventoryApi\Api\ReservationsAppendInterface;

/**
 * Plugin help to fill the legacy catalog inventory tables cataloginventory_stock_status and
 * cataloginventory_stock_item to don't break the backward compatible.
 */
class LegacyCatalogInventoryPlugin
{

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Plugin method to fill the legacy tables.
     *
     * @param ReservationsAppendInterface $subject
     * @param callable $callable
     * @param ReservationInterface[] $reservations
     * @return mixed
     * @see ReservationsAppendInterface::execute
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(ReservationsAppendInterface $subject, callable $callable, array $reservations)
    {
        $result = $callable($reservations);
        $this->updateStockItemTable($reservations);
        $this->updateStockStatusTable($reservations);
        return $result;
    }

    /**
     * Update cataloginventory_stock_item qty with reservation information.
     *
     * @param ReservationInterface[] $reservations
     * @return void
     */
    private function updateStockItemTable(array $reservations)
    {
        // @todo impelemention reqiured
    }

    /**
     * Update cataloginventory_stock_status qty with reservation information.
     *
     * @param ReservationInterface[] $reservations
     * @return void
     */
    private function updateStockStatusTable(array $reservations)
    {
        // @todo impelemention reqiured
    }
}
