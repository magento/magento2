<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShippingAdminUi\Model;

use Magento\Framework\App\RequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;

interface InventoryRequestBuilderInterface
{
    /**
     * Source selection results provider
     *
     * @param RequestInterface $request
     * @return InventoryRequestInterface
     */
    public function execute(RequestInterface $request): InventoryRequestInterface;
}
