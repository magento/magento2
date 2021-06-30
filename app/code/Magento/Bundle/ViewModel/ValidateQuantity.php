<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\ViewModel;

use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Block\Product\View as ProductView;

/**
 * ViewModel for Bundle Option Block
 */

class ValidateQuantity implements ArgumentInterface
{
    /**
     * @var ProductView
     */
    private $productView;

    /**
     * @param ProductView $productView
     */
    public function __construct(ProductView $productView)
    {
        $this->productView = $productView;
    }

    public function getQuantityValidators(): array
    {
        return $this->productView->getQuantityValidators();
    }
}
