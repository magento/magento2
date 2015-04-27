<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Model\Plugin;

class AroundProductRepositorySave
{
    /**
     * @var \Magento\ConfigurableProduct\Api\OptionRepositoryInterface
     */
    protected $optionRepository;

    /**
     * Type configurable factory
     *
     * @var \Magento\ConfigurableProduct\Model\Resource\Product\Type\ConfigurableFactory
     */
    protected $typeConfigurableFactory;

    /*
     * @var \Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute\Price\Data
     */
    protected $priceData;

    /**
     * @param \Magento\ConfigurableProduct\Api\OptionRepositoryInterface $optionRepository
     * @param \Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute\Price\Data $priceData
     * @param \Magento\ConfigurableProduct\Model\Resource\Product\Type\ConfigurableFactory $typeConfigurableFactory
     */
    public function __construct(
        \Magento\ConfigurableProduct\Api\OptionRepositoryInterface $optionRepository,
        \Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute\Price\Data $priceData,
        \Magento\ConfigurableProduct\Model\Resource\Product\Type\ConfigurableFactory $typeConfigurableFactory
    ) {
        $this->optionRepository = $optionRepository;
        $this->priceData = $priceData;
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
        }
        if ($configurableProductLinks !== null) {
            $this->saveConfigurableProductLinks($result, $configurableProductLinks);
        }
        $this->priceData->setProductPrice($result->getId(), null);
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
     * @param $links
     * @return $this
     */
    protected function saveConfigurableProductLinks(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        $links
    ) {
        $this->typeConfigurableFactory->create()->saveProducts($product, $links);
        return $this;
    }
}
