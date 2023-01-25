<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Block;

use Magento\Catalog\Api\Data\ProductTypeInterface;
use Magento\Catalog\Api\ProductTypeListInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Wishlist js plugin initialization block
 *
 * @api
 * @since 100.1.0
 */
class AddToWishlist extends Template
{
    /**
     * Product types
     *
     * @var array|null
     */
    private $productTypes;

    /**
     * @var ProductTypeListInterface
     */
    private $productTypeList;

    /**
     * AddToWishlist constructor.
     *
     * @param Context $context
     * @param array $data
     * @param ProductTypeListInterface|null $productTypeList
     */
    public function __construct(
        Context $context,
        array $data = [],
        ?ProductTypeListInterface $productTypeList = null
    ) {
        parent::__construct($context, $data);
        $this->productTypes = [];
        $this->productTypeList = $productTypeList ?: ObjectManager::getInstance()->get(ProductTypeListInterface::class);
    }

    /**
     * Returns wishlist widget options
     *
     * @return array
     * @since 100.1.0
     */
    public function getWishlistOptions()
    {
        return ['productType' => $this->getProductTypes()];
    }

    /**
     * Returns an array of product types
     *
     * @return array
     */
    private function getProductTypes(): array
    {
        if (count($this->productTypes) === 0) {
            /** @var ProductTypeInterface productTypes */
            $this->productTypes = array_map(function ($productType) {
                return $productType->getName();
            }, $this->productTypeList->getProductTypes());
        }
        return $this->productTypes;
    }
}
