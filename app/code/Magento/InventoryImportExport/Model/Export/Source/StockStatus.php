<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryImportExport\Model\Export\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Inventory\Model\OptionSource\SourceItemStatus;

/**
 * @inheritdoc
 */
class StockStatus extends AbstractSource
{
    /**
     * @var SourceItemStatus
     */
    private $sourceItemStatus;

    /**
     * @param SourceItemStatus $sourceItemStatus
     */
    public function __construct(
        SourceItemStatus $sourceItemStatus
    ) {
        $this->sourceItemStatus = $sourceItemStatus;
    }

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        return $this->sourceItemStatus->toOptionArray();
    }
}
