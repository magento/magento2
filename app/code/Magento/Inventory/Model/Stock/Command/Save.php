<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\Stock\Command;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Validation\ValidationException;
use Magento\Inventory\Model\ResourceModel\Stock as StockResourceModel;
use Magento\InventoryApi\Model\StockValidatorInterface;
use Magento\InventoryApi\Api\Data\StockInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class Save implements SaveInterface
{
    /**
     * @var StockValidatorInterface
     */
    private $stockValidator;

    /**
     * @var StockResourceModel
     */
    private $stockResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param StockValidatorInterface $stockValidator
     * @param StockResourceModel $stockResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        StockValidatorInterface $stockValidator,
        StockResourceModel $stockResource,
        LoggerInterface $logger
    ) {
        $this->stockValidator = $stockValidator;
        $this->stockResource = $stockResource;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(StockInterface $stock): int
    {
        $validationResult = $this->stockValidator->validate($stock);
        if (!$validationResult->isValid()) {
            throw new ValidationException(__('Validation Failed'), null, 0, $validationResult);
        }

        try {
            $this->stockResource->save($stock);
            return (int)$stock->getStockId();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save Stock'), $e);
        }
    }
}
