<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Model;

use Magento\InventorySourceSelectionApi\Api\GetDefaultSourceSelectionAlgorithmCodeInterface;

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
