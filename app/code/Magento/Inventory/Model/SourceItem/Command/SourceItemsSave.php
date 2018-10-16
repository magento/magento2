<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\SourceItem\Command;

use Magento\Inventory\Model\SourceItem\Command\Handler\SourceItemsSaveHandler;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;

/**
 * @inheritdoc
 */
class SourceItemsSave implements SourceItemsSaveInterface
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
    public function execute(array $sourceItems): void
    {
        $this->sourceItemsSaveHandler->execute($sourceItems);
    }
}
