<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryImportExport\Model\Import\Command;

use Magento\InventoryApi\Api\SourceItemsDeleteInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryImportExport\Helper\SourceItemConvert;

/**
 * @inheritdoc
 */
class Replace implements CommandInterface
{
    /**
     * @var SourceItemConvert
     */
    private $sourceItemConvert;

    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSave;

    /**
     * @param SourceItemConvert $sourceItemFactory
     * @param SourceItemsSaveInterface $sourceItemsSave
     * @param SourceItemsDeleteInterface $sourceItemsDelete
     */
    public function __construct(
        SourceItemConvert $sourceItemConvert,
        SourceItemsSaveInterface $sourceItemsSave
    ) {
        $this->sourceItemConvert = $sourceItemConvert;
        $this->sourceItemsSave = $sourceItemsSave;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $bunch)
    {
        // @todo implement clenup (DELETE from inventory_source_item;)
        $sourceItems = $this->sourceItemConvert->convert($bunch);
        $this->sourceItemsSave->execute($sourceItems);
    }
}
