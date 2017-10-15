<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\SourceItem\Command;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Inventory\Model\ResourceModel\SourceItem\SaveMultiple;
use Magento\Inventory\Model\Source\Validator\SourceItemValidatorInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class SourceItemsSave implements SourceItemsSaveInterface
{
    /**
     * @var SourceItemValidatorInterface
     */
    private $sourceItemValidator;

    /**
     * @var SaveMultiple
     */
    private $saveMultiple;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param SourceItemValidatorInterface $sourceItemValidator
     * @param SaveMultiple $saveMultiple
     * @param LoggerInterface $logger
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        SourceItemValidatorInterface $sourceItemValidator,
        SaveMultiple $saveMultiple,
        LoggerInterface $logger
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->sourceItemValidator = $sourceItemValidator;
        $this->saveMultiple = $saveMultiple;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function execute(array $sourceItems)
    {
        if (empty($sourceItems)) {
            throw new InputException(__('Input data is empty'));
        }

        // @todo should this be moved because of single responsibility
        $errors = [];
        foreach ($sourceItems as $sourceItem) {
            $validationResult = $this->sourceItemValidator->validate($sourceItem);

            if (!$validationResult->isValid()) {
                $errors = array_merge($errors, $validationResult->getErrors());
            }
        }

        if (count($errors)) {
            $validationResult = $this->validationResultFactory->create(['errors' => $errors]);
            throw new ValidationException(__('Validation Failed'), null, 0, $validationResult);
        }

        try {
            $this->saveMultiple->execute($sourceItems);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save Source Item'), $e);
        }
    }
}
