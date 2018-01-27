<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\StockSourceLink\Command;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Inventory\Model\ResourceModel\StockSourceLink\SaveMultiple;
use Magento\Inventory\Model\StockSourceLink\Validator\StockSourceLinkValidatorInterface;
use Magento\InventoryApi\Api\AssignSourcesToStockInterface;
use Magento\Framework\Validation\ValidationException;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class AssignSourcesToStock implements AssignSourcesToStockInterface
{
    /**
     * @var SaveMultiple
     */
    private $saveMultiple;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StockSourceLinkValidatorInterface
     */
    private $stockSourceLinkValidator;

    /**
     * AssignSourcesToStock constructor.
     * @param SaveMultiple $saveMultiple
     * @param LoggerInterface $logger
     * @param StockSourceLinkValidatorInterface $stockSourceLinkValidator
     */
    public function __construct(
        SaveMultiple $saveMultiple,
        LoggerInterface $logger,
        StockSourceLinkValidatorInterface $stockSourceLinkValidator
    ) {
        $this->saveMultiple = $saveMultiple;
        $this->logger = $logger;
        $this->stockSourceLinkValidator = $stockSourceLinkValidator;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $sourceCodes, int $stockId)
    {
        if (empty($sourceCodes)) {
            throw new InputException(__('Input data is invalid'));
        }
        $validationResult = $this->stockSourceLinkValidator->validate($sourceCodes, $stockId);
        if (!$validationResult->isValid()) {
            throw new ValidationException(__('Validation Failed'), null, 0, $validationResult);
        }
        try {
            $this->saveMultiple->execute($sourceCodes, $stockId);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not assign Sources to Stock'), $e);
        }
    }
}
