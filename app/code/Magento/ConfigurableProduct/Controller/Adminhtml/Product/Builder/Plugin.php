<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Builder;

use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Controller\Adminhtml\Product\Builder as CatalogProductBuilder;
use Magento\Framework\App\RequestInterface;

class Plugin
{
    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurableType;

    /**
     * @param ProductFactory $productFactory
     * @param Type\Configurable $configurableType
     */
    public function __construct(ProductFactory $productFactory, Type\Configurable $configurableType)
    {
        $this->productFactory = $productFactory;
        $this->configurableType = $configurableType;
    }

    /**
     * Set type and data to configurable product
     *
     * @param CatalogProductBuilder $subject
     * @param Product $product
     * @param RequestInterface $request
     * @return Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterBuild(CatalogProductBuilder $subject, Product $product, RequestInterface $request)
    {
        $this->setProductType($product, $request);
        $this->setRequiredAttributes($product, $request);
        $this->copyAttributesFromConfigurable($product, $request);

        return $product;
    }

    /**
     * Set product type based on request attributes
     *
     * @param Product $product
     * @param RequestInterface $request
     * @return void
     */
    private function setProductType(Product $product, RequestInterface $request): void
    {
        if ($request->has('attributes')) {
            $attributes = $request->getParam('attributes');
            if (!empty($attributes)) {
                $product->setTypeId(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);
                $this->configurableType->setUsedProductAttributes($product, $attributes);
            } else {
                // Preserve the configurable type if product is already configurable
                // Only convert to simple if the product was not previously a configurable product
                $configurableTypeCode = \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE;
                if ($product->getOrigData('type_id') !== $configurableTypeCode) {
                    $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
                }
            }
        }
    }

    /**
     * Set required attributes on product
     *
     * @param Product $product
     * @param RequestInterface $request
     * @return void
     */
    private function setRequiredAttributes(Product $product, RequestInterface $request): void
    {
        // Required attributes of simple product for configurable creation
        if ($request->getParam('popup') && ($requiredAttributes = $request->getParam('required'))) {
            $requiredAttributes = explode(",", $requiredAttributes);
            foreach ($product->getAttributes() as $attribute) {
                if (in_array($attribute->getId(), $requiredAttributes)) {
                    $attribute->setIsRequired(1);
                }
            }
        }
    }

    /**
     * Copy attributes from configurable product
     *
     * @param Product $product
     * @param RequestInterface $request
     * @return void
     */
    private function copyAttributesFromConfigurable(Product $product, RequestInterface $request): void
    {
        if (!$this->shouldCopyAttributes($request)) {
            return;
        }

        $configProduct = $this->productFactory->create();
        $configProduct->setStoreId(0)
            ->load($request->getParam('product'))
            ->setTypeId($request->getParam('type'));

        $data = [];
        foreach ($configProduct->getTypeInstance()->getSetAttributes($configProduct) as $attribute) {
            /* @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
            if ($this->isAttributeValuable($attribute, $configProduct)) {
                $data[$attribute->getAttributeCode()] = $configProduct->getData($attribute->getAttributeCode());
            }
        }
        $product->addData($data);
        $product->setWebsiteIds($configProduct->getWebsiteIds());
    }

    /**
     * Check if attributes should be copied from configurable product
     *
     * @param RequestInterface $request
     * @return bool
     */
    private function shouldCopyAttributes(RequestInterface $request): bool
    {
        return $request->getParam('popup')
            && $request->getParam('product')
            && !is_array($request->getParam('product'))
            && $request->getParam('id', false) === false;
    }

    /**
     * Check if attribute value should be copied
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @param Product $configProduct
     * @return bool
     */
    private function isAttributeValuable(
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute,
        $configProduct
    ): bool {
        return !$attribute->getIsUnique() &&
            $attribute->getFrontend()->getInputType() != 'gallery' &&
            $attribute->getAttributeCode() != 'required_options' &&
            $attribute->getAttributeCode() != 'has_options' &&
            $attribute->getAttributeCode() != $configProduct->getIdFieldName();
    }
}
