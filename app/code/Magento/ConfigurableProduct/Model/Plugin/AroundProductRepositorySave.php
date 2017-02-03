<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Model\Plugin;

use Magento\Framework\Exception\InputException;

class AroundProductRepositorySave
{
    /**
     * @var \Magento\ConfigurableProduct\Api\OptionRepositoryInterface
     */
    protected $optionRepository;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * Type configurable factory
     *
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory
     */
    protected $typeConfigurableFactory;

    /**
     * @param \Magento\ConfigurableProduct\Api\OptionRepositoryInterface $optionRepository
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory $typeConfigurableFactory
     */
    public function __construct(
        \Magento\ConfigurableProduct\Api\OptionRepositoryInterface $optionRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory $typeConfigurableFactory
    ) {
        $this->optionRepository = $optionRepository;
        $this->productFactory = $productFactory;
        $this->typeConfigurableFactory = $typeConfigurableFactory;
    }

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $subject
     * @param callable $proceed
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param bool $saveOptions
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Closure $proceed,
        \Magento\Catalog\Api\Data\ProductInterface $product,
        $saveOptions = false
    ) {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $result */
        $result = $proceed($product, $saveOptions);

        if ($product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return $result;
        }

        $extendedAttributes = $product->getExtensionAttributes();
        if ($extendedAttributes === null) {
            return $result;
        }
        $configurableProductOptions = $extendedAttributes->getConfigurableProductOptions();
        $configurableProductLinks = $extendedAttributes->getConfigurableProductLinks();
        if ($configurableProductOptions === null && $configurableProductLinks === null) {
            return $result;
        }
        if ($configurableProductOptions !== null) {
            $this->saveConfigurableProductOptions($result, $configurableProductOptions);
            $result->getTypeInstance()->resetConfigurableAttributes($result);
        }
        if ($configurableProductLinks !== null) {
            $this->saveConfigurableProductLinks($result, $configurableProductLinks);
        }
        return $subject->get($result->getSku(), false, $result->getStoreId(), true);
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Magento\ConfigurableProduct\Api\Data\OptionInterface[] $options
     * @return $this
     */
    protected function saveConfigurableProductOptions(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        array $options
    ) {
        $existingOptionIds = [];
        if ($product->getExtensionAttributes() !== null) {
            $extensionAttributes = $product->getExtensionAttributes();
            if ($extensionAttributes->getConfigurableProductOptions() !== null) {
                $existingOptions = $extensionAttributes->getConfigurableProductOptions();
                foreach ($existingOptions as $option) {
                    $existingOptionIds[] = $option->getId();
                }
            }
        }

        $updatedOptionIds = [];
        foreach ($options as $option) {
            if ($option->getId()) {
                $updatedOptionIds[] = $option->getId();
            }
            $this->optionRepository->save($product->getSku(), $option);
        }

        $optionIdsToDelete = array_diff($existingOptionIds, $updatedOptionIds);
        foreach ($optionIdsToDelete as $optionId) {
            $this->optionRepository->deleteById($product->getSku(), $optionId);
        }
        return $this;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param int[] $linkIds
     * @return $this
     */
    protected function saveConfigurableProductLinks(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        array $linkIds
    ) {
        $configurableProductTypeResource = $this->typeConfigurableFactory->create();
        if (!empty($linkIds)) {
            /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProductType */
            $configurableProductType = $product->getTypeInstance();
            $configurableAttributes = $configurableProductType->getConfigurableAttributes($product);
            $attributeCodes = [];
            foreach ($configurableAttributes as $configurableAttribute) {
                /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $productAttribute */
                $productAttribute = $configurableAttribute->getProductAttribute();
                $attributeCode = $productAttribute->getAttributeCode();
                $attributeCodes[] = $attributeCode;
            }
            $this->validateProductLinks($attributeCodes, $linkIds);
        }

        $configurableProductTypeResource->saveProducts($product, $linkIds);
        $product->getTypeInstance()->resetConfigurableAttributes($product);
        return $this;
    }

    /**
     * @param array $attributeCodes
     * @param array $linkIds
     * @throws InputException
     * @return $this
     */
    protected function validateProductLinks(array $attributeCodes, array $linkIds)
    {
        $valueMap = [];
        if (empty($attributeCodes) && !empty($linkIds)) {
            throw new InputException(
                __('The configurable product does not have any variation attribute.')
            );
        }

        foreach ($linkIds as $productId) {
            $variation = $this->productFactory->create()->load($productId);
            if (!$variation->getId()) {
                throw new InputException(__('Product with id "%1" does not exist.', $productId));
            }
            $valueKey = '';
            foreach ($attributeCodes as $attributeCode) {
                if (!$variation->getData($attributeCode)) {
                    throw new InputException(
                        __('Product with id "%1" does not contain required attribute "%2".', $productId, $attributeCode)
                    );
                }
                $valueKey = $valueKey . $attributeCode . ':' . $variation->getData($attributeCode) . ';';
            }
            if (isset($valueMap[$valueKey])) {
                throw new InputException(
                    __('Products "%1" and %2 have the same set of attribute values.', $productId, $valueMap[$valueKey])
                );
            }
            $valueMap[$valueKey] = $productId;
        }
        return $this;
    }
}
