<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model\ShippingAlgorithmResult;

/**
 * Represents shipping algorithm results for the specific source
 *
 * @api
 */
interface SourceSelectionInterface
{
    /**
     * Get source code
     *
     * @return string
     */
    public function getSourceCode(): string;

    /**
     * @return SourceItemSelectionInterface[]
     */
    public function getSourceItemSelections(): array;
}
