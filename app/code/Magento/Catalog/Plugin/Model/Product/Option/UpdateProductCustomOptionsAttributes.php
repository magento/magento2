<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Plugin\Model\Product\Option;

/**
 * Plugin for updating product 'has_options' and 'required_options' attributes
 */
class UpdateProductCustomOptionsAttributes
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(\Magento\Catalog\Api\ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Update product 'has_options' and 'required_options' attributes after option save
     *
     * @param \Magento\Catalog\Api\ProductCustomOptionRepositoryInterface $subject
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionInterface $option
     *
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        \Magento\Catalog\Api\ProductCustomOptionRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductCustomOptionInterface $option
    ) {
        $product = $this->productRepository->get($option->getProductSku());
        if (!$product->getHasOptions() ||
            ($option->getIsRequire() && !$product->getRequiredOptions())) {
            $product->setCanSaveCustomOptions(true);
            $product->setOptionsSaved(true);
            $currentOptions = array_filter($product->getOptions(), function ($iOption) use ($option) {
                return $option->getOptionId() != $iOption->getOptionId();
            });
            $currentOptions[] = $option;
            $product->setOptions($currentOptions);
            $product->save();
        }

        return $option;
    }
}
