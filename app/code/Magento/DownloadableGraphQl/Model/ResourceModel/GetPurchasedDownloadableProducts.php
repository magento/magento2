<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableGraphQl\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Downloadable\Model\Link\Purchased\Item;

/**
 * The model returns all purchased products for the specified customer
 */
class GetPurchasedDownloadableProducts
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Return available purchased products for customer
     *
     * @param int $customerId
     * @return array
     */
    public function execute(int $customerId): array
    {
        $connection = $this->resourceConnection->getConnection();
        $allowedItemsStatuses = [Item::LINK_STATUS_PENDING_PAYMENT, Item::LINK_STATUS_PAYMENT_REVIEW];
        $downloadablePurchasedTable = $this->resourceConnection->getTableName('downloadable_link_purchased');

        /* The fields names are hardcoded since there's no existing name reference in the code */
        $selectQuery = $connection->select()
            ->from($downloadablePurchasedTable)
            ->joinLeft(
                ['item' => $this->resourceConnection->getTableName('downloadable_link_purchased_item')],
                "$downloadablePurchasedTable.purchased_id = item.purchased_id"
            )
            ->where("$downloadablePurchasedTable.customer_id = ?", $customerId)
            ->where('item.status NOT IN (?)', $allowedItemsStatuses);

        return $connection->fetchAll($selectQuery);
    }
}
