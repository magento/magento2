<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\StockSourceLink\Command;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Validation\ValidationException;
use Magento\Inventory\Model\ResourceModel\StockSourceLink as StockSourceLinkResourceModel;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\InventoryApi\Api\StockSourceLinksSaveInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class StockSourceLinksSave implements StockSourceLinksSaveInterface
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
    public function execute(array $links): array
    {
        $savedLinkIdList = [];

        try {
            foreach ($links as $link) {
                $this->stockSourceLinkResource->save($link);
                $savedLinkIdList[] = (int)$link->getStockId();
            }

            return $savedLinkIdList;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save Stock Source Link'), $e);
        }
    }
}
