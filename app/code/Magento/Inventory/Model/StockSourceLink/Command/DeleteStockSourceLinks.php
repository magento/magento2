<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\StockSourceLink\Command;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Inventory\Model\ResourceModel\StockSourceLink as StockSourceLinkResourceModel;
use Magento\InventoryApi\Api\StockSourceLinksDeleteInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class DeleteStockSourceLinks implements StockSourceLinksDeleteInterface
{
    /**
     * @var StockSourceLinkResourceModel
     */
    private $stockSourceLinkResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param StockSourceLinkResourceModel $stockSourceLinkResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        StockSourceLinkResourceModel $stockSourceLinkResource,
        LoggerInterface $logger
    ) {
        $this->stockSourceLinkResource = $stockSourceLinkResource;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $links)
    {
        try {
            foreach ($links as $link) {
                $this->stockSourceLinkResource->delete($link);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotDeleteException(__('Could not delete Stock Source Link'), $e);
        }
    }
}
