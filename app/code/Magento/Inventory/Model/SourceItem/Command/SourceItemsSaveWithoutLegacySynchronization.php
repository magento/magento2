<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\SourceItem\Command;

use Magento\Inventory\Model\SourceItem\Command\Handler\SourceItemsSaveHandler;
use Magento\InventoryApi\Model\SourceItemsSaveWithoutLegacySynchronizationInterface;

/**
 * @inheritdoc
 */
class SourceItemsSaveWithoutLegacySynchronization implements SourceItemsSaveWithoutLegacySynchronizationInterface
{
    /**
     * @var SourceItemsSaveHandler
     */
    private $sourceItemsSaveHandler;

    /**
     * @param SourceItemsSaveHandler $sourceItemsSaveHandler
     */
    public function __construct(SourceItemsSaveHandler $sourceItemsSaveHandler)
    {
        $this->sourceItemsSaveHandler = $sourceItemsSaveHandler;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $sourceItems)
    {
        $this->sourceItemsSaveHandler->execute($sourceItems);
    }
}
