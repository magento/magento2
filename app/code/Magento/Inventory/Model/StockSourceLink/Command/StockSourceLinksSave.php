<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\StockSourceLink\Command;

use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Inventory\Model\ResourceModel\StockSourceLink\SaveMultiple;
use Magento\Inventory\Model\StockSourceLink;
use Magento\Inventory\Model\StockSourceLink\Validator\StockSourceLinkValidatorInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryApi\Api\StockSourceLinksSaveInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class StockSourceLinksSave implements StockSourceLinksSaveInterface
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
     * @param StockSourceLink[] $links
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws ValidationException
     */
    public function execute(array $links)
    {
        if (empty($links)) {
            throw new InputException(__('Input data is empty'));
        }

        $validationResult = $this->stockSourceLinkValidator->validate($links);
        if (!$validationResult->isValid()) {
            throw new ValidationException(__('Validation Failed'), null, 0, $validationResult);
        }

        try {
            $this->saveMultiple->execute($links);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save StockSourceLinks'), $e);
        }
    }
}
