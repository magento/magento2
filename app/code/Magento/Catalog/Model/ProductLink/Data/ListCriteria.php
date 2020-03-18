<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ProductLink\Data;

use Magento\Catalog\Model\Product;

/**
 * @inheritDoc
 */
class ListCriteria implements ListCriteriaInterface
{
    /**
     * @var string
     */
    private $productSku;

    /**
     * @var Product|null
     */
    private $product;

    /**
     * @var string[]|null
     */
    private $linkTypes;

    /**
     * ListCriteria constructor.
     * @param string $belongsToProductSku
     * @param string[]|null $linkTypes
     * @param Product|null $belongsToProduct
     */
    public function __construct(
        string $belongsToProductSku,
        ?array $linkTypes = null,
        ?Product $belongsToProduct = null
    ) {
        $this->productSku = $belongsToProductSku;
        $this->linkTypes = $linkTypes;
        if ($belongsToProduct) {
            $this->productSku = $belongsToProduct->getSku();
            $this->product = $belongsToProduct;
        }
    }

    /**
     * @inheritDoc
     */
    public function getBelongsToProductSku(): string
    {
        return $this->productSku;
    }

    /**
     * @inheritDoc
     */
    public function getLinkTypes(): ?array
    {
        return $this->linkTypes;
    }

    /**
     * Product model.
     *
     * @see getBelongsToProductSku()
     * @return Product|null
     */
    public function getBelongsToProduct(): ?Product
    {
        return $this->product;
    }
}
