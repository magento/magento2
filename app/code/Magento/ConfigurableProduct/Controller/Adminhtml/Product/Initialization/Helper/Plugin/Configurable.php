<?php
/**
 * Product initialzation helper
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;

class Configurable
{
    /** @var \Magento\ConfigurableProduct\Model\Product\VariationHandler */
    protected $variationHandler;

    /** @var \Magento\Framework\App\RequestInterface */
    protected $request;

    /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable */
    protected $productType;

    /**
     * @param \Magento\ConfigurableProduct\Model\Product\VariationHandler $variationHandler
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productType
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\ConfigurableProduct\Model\Product\VariationHandler $variationHandler,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productType,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->variationHandler = $variationHandler;
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
        if ($product->getTypeId() == ConfigurableProduct::TYPE_CODE && !empty($attributes)) {
            $setId = $this->request->getPost('new-variations-attribute-set-id');
            $product->setAttributeSetId($setId);
            $this->productType->setUsedProductAttributeIds($attributes, $product);

            $product->setNewVariationsAttributeSetId($setId);
            $associatedProductIds = $this->request->getPost('associated_product_ids_serialized', '[]');
            if ($associatedProductIds !== null && !empty($associatedProductIds)) {
                $associatedProductIds = json_decode($associatedProductIds, true);
            }
            $variationsMatrix = $this->request->getParam('configurable-matrix-serialized', '[]');
            if ($variationsMatrix !== null && !empty($variationsMatrix)) {
                $variationsMatrix = json_decode($variationsMatrix, true);
            }
            if (!empty($variationsMatrix)) {
                $generatedProductIds = $this->variationHandler->generateSimpleProducts($product, $variationsMatrix);
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
