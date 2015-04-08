<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Plugin;

class BundleSaveOptions
{
    /**
     * @var \Magento\Bundle\Api\ProductOptionRepositoryInterface
     */
    protected $optionRepository;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterfaceFactory
     */
    protected $productInterfaceFactory;

    /**
     * @param \Magento\Bundle\Api\ProductOptionRepositoryInterface $optionRepository
     * @param \Magento\Catalog\Api\Data\ProductInterfaceFactory $productInterfaceFactory
     */
    public function __construct(
        \Magento\Bundle\Api\ProductOptionRepositoryInterface $optionRepository,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productInterfaceFactory
    ) {
        $this->optionRepository = $optionRepository;
        $this->productInterfaceFactory = $productInterfaceFactory;
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

        if ($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            return $result;
        }

        /* @var \Magento\Bundle\Api\Data\OptionInterface[] $options */
        $bundleProductOptions = $product->getExtensionAttributes()->getBundleProductOptions();
        if ($bundleProductOptions == null) {
            return $result;
        }

        /** @var \Magento\Bundle\Api\Data\OptionInterface[] $bundleProductOptions */
        $existingOptions = $this->optionRepository->getList($product->getSku());
        $existingOptionsMap = [];
        foreach ($existingOptions as $existingOption) {
            $existingOptionsMap[$existingOption->getOptionId()] = $existingOption;
        }
        $updatedOptionIds = [];
        foreach ($bundleProductOptions as $bundleOption) {
            $optionId = $bundleOption->getOptionId();
            if ($optionId) {
                $updatedOptionIds[] = $optionId;
            }
        }
        $optionIdsToDelete = array_diff(array_keys($existingOptionsMap), $updatedOptionIds);
        //Handle new and existing options
        foreach ($bundleProductOptions as $option) {
            $this->optionRepository->save($result, $option);
        }
        //Delete options that are not in the list
        foreach ($optionIdsToDelete as $optionId) {
            $this->optionRepository->delete($existingOptionsMap[$optionId]);
        }
        $product = $this->productInterfaceFactory->create()->setSku($result->getSku());
        return $subject->save($product);
    }
}
