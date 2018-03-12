<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model\SourceSelection;

use Magento\InventoryShipping\Model\SourceSelection\GetDefaultSourceSelectionAlgorithmCodeInterface;

class GetDefaultSourceSelectionAlgorithmCode implements GetDefaultSourceSelectionAlgorithmCodeInterface
{
    /**
     * @inheritdoc
     */
    public function execute(): string
    {
        return 'priority';
    }
}
