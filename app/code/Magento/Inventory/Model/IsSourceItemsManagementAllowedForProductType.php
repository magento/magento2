<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model;

/**
 * @inheritdoc
 */
class IsSourceItemsManagementAllowedForProductType implements IsSourceItemsManagementAllowedForProductTypeInterface
{
    /**
     * @var array
     */
    private $allowedProductTypes = [];

    /**
     * @param array $allowedProductTypes
     */
    public function __construct(array $allowedProductTypes)
    {
        $this->allowedProductTypes = $allowedProductTypes;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $productType): bool
    {
        return in_array($productType, $this->allowedProductTypes, true);
    }
}
