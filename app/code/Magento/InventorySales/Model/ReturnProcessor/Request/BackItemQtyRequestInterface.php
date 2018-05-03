<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ReturnProcessor\Request;

interface BackItemQtyRequestInterface
{
    /**
     * @return string
     */
    public function getSourceCode(): string;

    /**
     * @return string
     */
    public function getSku(): string;

    /**
     * @return float
     */
    public function getQuantity(): float;
}
