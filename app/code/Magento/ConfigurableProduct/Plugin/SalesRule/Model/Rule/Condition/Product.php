<?php declare(strict_types=1);

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
     * @param \Magento\SalesRule\Model\Rule\Condition\Product $subject
     * @param \Magento\Framework\Model\AbstractModel          $model
     */
    public function beforeValidate(
        \Magento\SalesRule\Model\Rule\Condition\Product $subject,
        \Magento\Framework\Model\AbstractModel $model
    ) {
        $model->setProduct(
            $this->getProductToValidate($subject, $model)
        );
    }


    /**
     * @param \Magento\SalesRule\Model\Rule\Condition\Product $subject
     * @param \Magento\Framework\Model\AbstractModel          $model
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
