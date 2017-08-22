<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\SourceItem\Command;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Inventory\Model\ResourceModel\SourceItem\SaveMultiple;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class SourceItemsSave implements SourceItemsSaveInterface
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
     * @param SaveMultiple $saveMultiple
     * @param LoggerInterface $logger
     */
    public function __construct(
        SaveMultiple $saveMultiple,
        LoggerInterface $logger
    ) {
        $this->saveMultiple = $saveMultiple;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $sourceItems)
    {
        if (empty($sourceItems)) {
            throw new InputException(__('Input data is empty'));
        }
        try {
            $this->saveMultiple->execute($sourceItems);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save Source Item'), $e);
        }
    }
}
