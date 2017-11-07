<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryImportExport\Model\Import\Command;

use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryImportExport\Model\Import\SourceItemConvert;

/**
 * @inheritdoc
 */
class Append implements CommandInterface
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
     * @param SourceItemConvert $sourceItemConvert
     * @param SourceItemsSaveInterface $sourceItemsSave
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
        $sourceItems = $this->sourceItemConvert->convert($bunch);
        $this->sourceItemsSave->execute($sourceItems);
    }
}
