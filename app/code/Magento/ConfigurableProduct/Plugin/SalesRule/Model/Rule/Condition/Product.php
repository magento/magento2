<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Plugin\SalesRule\Model\Rule\Condition;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class Product
 *
 * @package Magento\ConfigurableProduct\Plugin\SalesRule\Model\Rule\Condition
 */
class Product
{
    /**
     * Prepare configurable product for validation.
     *
     * @param \Magento\SalesRule\Model\Rule\Condition\Product $subject
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return array
     */
    public function beforeValidate(
        \Magento\SalesRule\Model\Rule\Condition\Product $subject,
        \Magento\Framework\Model\AbstractModel $model
    ) {
        $product = $this->getProductToValidate($subject, $model);
        if ($model->getProduct() !== $product) {
            // We need to replace product only for validation and keep original product for all other cases.
            $clone = clone $model;
            $clone->setProduct($product);
            $model = $clone;
        }

        return [$model];
    }

    /**
     * Select proper product for validation.
     *
     * @param \Magento\SalesRule\Model\Rule\Condition\Product $subject
     * @param \Magento\Framework\Model\AbstractModel $model
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product
     */
    private function getProductToValidate(
        \Magento\SalesRule\Model\Rule\Condition\Product $subject,
        \Magento\Framework\Model\AbstractModel $model
    ) {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $model->getProduct();

        $attrCode = $subject->getAttribute();

        /* Check for attributes which are not available for configurable products */
        if ($product->getTypeId() == Configurable::TYPE_CODE && !$product->hasData($attrCode)) {
            /** @var \Magento\Catalog\Model\AbstractModel $childProduct */
            $childProduct = current($model->getChildren())->getProduct();
            if ($childProduct->hasData($attrCode)) {
                $product = $childProduct;
            }
        }

        return $product;
    }
}
