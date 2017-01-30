<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Plugin;

class BundleLoadOptions
{
    /**
     * @var \Magento\Bundle\Model\Product\OptionList
     */
    protected $productOptionList;

    /**
     * @var \Magento\Catalog\Api\Data\ProductExtensionFactory
     */
    protected $productExtensionFactory;

    /**
     * @param \Magento\Bundle\Model\Product\OptionList $productOptionList
     * @param \Magento\Framework\Api\AttributeDataBuilder $customAttributeBuilder
     * @param \Magento\Catalog\Api\Data\ProductExtensionFactory $productExtensionFactory
     */
    public function __construct(
        \Magento\Bundle\Model\Product\OptionList $productOptionList,
        \Magento\Catalog\Api\Data\ProductExtensionFactory $productExtensionFactory
    ) {
        $this->productOptionList = $productOptionList;
        $this->productExtensionFactory = $productExtensionFactory;
    }

    /**
     * @param \Magento\Catalog\Model\Product $subject
     * @param callable $proceed
     * @param int $modelId
     * @param null $field
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundLoad(
        \Magento\Catalog\Model\Product $subject,
        \Closure $proceed,
        $modelId,
        $field = null
    ) {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $proceed($modelId, $field);
        if ($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            return $product;
        }

        $productExtension = $product->getExtensionAttributes();
        if ($productExtension === null) {
            $productExtension = $this->productExtensionFactory->create();
        }
        $productExtension->setBundleProductOptions($this->productOptionList->getItems($product));

        $product->setExtensionAttributes($productExtension);

        return $product;
    }
}
