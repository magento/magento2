<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\Source\Command;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Inventory\Model\ResourceModel\Source as SourceResourceModel;
use Magento\Inventory\Model\Source\Validator\ValidatorChain;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class Save implements SaveInterface
{
    /**
     * TODO: replace on interface
     * @var SourceValidatorInterface
     */
    private $validatorChain;

    /**
     * @var SourceResourceModel
     */
    private $sourceResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ValidatorChain $validatorChain
     * @param SourceResourceModel $sourceResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        SourceValidatorInterface $validatorChain,
        SourceResourceModel $sourceResource,
        LoggerInterface $logger
    ) {
        $this->validatorChain = $validatorChain;
        $this->sourceResource = $sourceResource;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(SourceInterface $source)
    {
        $validationResult = $this->validatorChain->validate($source);

        if (!$validationResult->isValid()) {
            throw new \Magento\Framework\Validation\ValidationException($validationResult->getErrors());
        }

        // TODO: check if exists?
        try {
            $this->sourceResource->save($source);
            return $source->getSourceId();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save Source'), $e);
        }
    }
}
