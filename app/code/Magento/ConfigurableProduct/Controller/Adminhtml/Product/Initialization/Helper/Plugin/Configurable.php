<?php
/**
 * Product initialzation helper
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

class Configurable
{
    /**
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productType
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productType,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->productType = $productType;
        $this->request = $request;
    }

    /**
     * Initialize data for configurable product
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterInitialize(
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject,
        \Magento\Catalog\Model\Product $product
    ) {
        $attributes = $this->request->getParam('attributes');
        if (!empty($attributes)) {
            $this->productType->setUsedProductAttributeIds($attributes, $product);

            $product->setNewVariationsAttributeSetId($this->request->getPost('new-variations-attribute-set-id'));
            $associatedProductIds = $this->request->getPost('associated_product_ids', []);
            $variationsMatrix = $this->request->getParam('variations-matrix', []);
            if (!empty($variationsMatrix)) {
                $generatedProductIds = $this->productType->generateSimpleProducts($product, $variationsMatrix);
                $associatedProductIds = array_merge($associatedProductIds, $generatedProductIds);
            }
            $product->setAssociatedProductIds(array_filter($associatedProductIds));

            $product->setCanSaveConfigurableAttributes(
                (bool)$this->request->getPost('affect_configurable_product_attributes')
            );
        }

        return $product;
    }
}
