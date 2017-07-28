<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\Source\Command;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Inventory\Model\ResourceModel\Source as SourceResourceModel;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class Save implements SaveInterface
{
    /**
     * @var SourceResourceModel
     */
    private $sourceResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param SourceResourceModel $sourceResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        SourceResourceModel $sourceResource,
        LoggerInterface $logger
    ) {
        $this->sourceResource = $sourceResource;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(SourceInterface $source)
    {
        try {
            $this->sourceResource->save($source);
            return $source->getSourceId();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save Source'), $e);
        }
    }
}
