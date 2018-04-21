<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryImportExport\Model\Import\Command;

use Magento\InventoryApi\Api\SourceItemsDeleteInterface;
use Magento\InventoryImportExport\Model\Import\SourceItemConvert;

/**
 * @inheritdoc
 */
class Delete implements CommandInterface
{

    /**
     * @var SourceItemConvert
     */
    private $sourceItemConvert;

    /**
     * @var SourceItemsDeleteInterface
     */
    private $sourceItemsDelete;

    /**
     * @param SourceItemConvert $sourceItemConvert
     * @param SourceItemsDeleteInterface $sourceItemsDelete
     */
    public function __construct(
        SourceItemConvert $sourceItemConvert,
        SourceItemsDeleteInterface $sourceItemsDelete
    ) {
        $this->sourceItemConvert = $sourceItemConvert;
        $this->sourceItemsDelete = $sourceItemsDelete;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $bunch)
    {
        $sourceItems = $this->sourceItemConvert->convert($bunch);
        $this->sourceItemsDelete->execute($sourceItems);
    }
}
