<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\Source\Command;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Validation\ValidationException;
use Magento\Inventory\Model\ResourceModel\Source as SourceResourceModel;
use Magento\InventoryApi\Model\SourceValidatorInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class Save implements SaveInterface
{
    /**
     * @var SourceValidatorInterface
     */
    private $sourceValidator;

    /**
     * @var SourceResourceModel
     */
    private $sourceResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param SourceValidatorInterface $sourceValidator
     * @param SourceResourceModel $sourceResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        SourceValidatorInterface $sourceValidator,
        SourceResourceModel $sourceResource,
        LoggerInterface $logger
    ) {
        $this->sourceValidator = $sourceValidator;
        $this->sourceResource = $sourceResource;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(SourceInterface $source)
    {
        $validationResult = $this->sourceValidator->validate($source);

        if (!$validationResult->isValid()) {
            throw new ValidationException(__('Validation Failed'), null, 0, $validationResult);
        }

        try {
            $this->sourceResource->save($source);
            $source->getSourceCode();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save Source'), $e);
        }
    }
}
