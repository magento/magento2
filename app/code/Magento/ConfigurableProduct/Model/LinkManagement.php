<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model;

use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Webapi\Exception;

class LinkManagement implements \Magento\ConfigurableProduct\Api\LinkManagementInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Api\Data\ProductDataBuilder
     */
    private $productBuilder;

    /**
     * @var Resource\Product\Type\Configurable
     */
    private $configurableType;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\Data\ProductDataBuilder $productBuilder
     * @param Resource\Product\Type\Configurable $configurableType
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\Data\ProductDataBuilder $productBuilder,
        \Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable $configurableType
    ) {
        $this->productRepository = $productRepository;
        $this->productBuilder = $productBuilder;
        $this->configurableType = $configurableType;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren($productSku)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get($productSku);
        if ($product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return [];
        }

        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productTypeInstance */
        $productTypeInstance = $product->getTypeInstance();
        $productTypeInstance->setStoreFilter($product->getStoreId(), $product);

        $childrenList = [];
        /** @var \Magento\Catalog\Model\Product $child */
        foreach ($productTypeInstance->getUsedProducts($product) as $child) {
            $attributes = [];
            foreach ($child->getAttributes() as $attribute) {
                $attrCode = $attribute->getAttributeCode();
                $value = $child->getDataUsingMethod($attrCode) ?: $child->getData($attrCode);
                if (null !== $value && $attrCode != 'entity_id') {
                    $attributes[$attrCode] = $value;
                }
            }
            $attributes['store_id'] = $child->getStoreId();
            $childrenList[] = $this->productBuilder->populateWithArray($attributes)->create();
        }
        return $childrenList;
    }

    /**
     * {@inheritdoc}
     */
    public function addChild($productSku, $childSku)
    {
        $product = $this->productRepository->get($productSku);
        $child = $this->productRepository->get($childSku);

        $childrenIds = array_values($this->configurableType->getChildrenIds($product->getId())[0]);
        if (in_array($child->getId(), $childrenIds)) {
            throw new StateException('Product has been already attached');
        }

        $childrenIds[] = $child->getId();
        $product->setAssociatedProductIds($childrenIds);
        $product->save();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function removeChild($productSku, $childSku)
    {
        $product = $this->productRepository->get($productSku);

        if ($product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            throw new Exception(
                sprintf('Product with specified sku: %s is not a configurable product', $productSku),
                Exception::HTTP_FORBIDDEN
            );
        }

        $options = $product->getTypeInstance()->getUsedProducts($product);
        $ids = [];
        foreach ($options as $option) {
            if ($option->getSku() == $childSku) {
                continue;
            }
            $ids[] = $option->getId();
        }
        if (count($options) == count($ids)) {
            throw new NoSuchEntityException('Requested option doesn\'t exist');
        }
        $product->addData(['associated_product_ids' => $ids]);
        $product->save();
        return true;
    }
}
