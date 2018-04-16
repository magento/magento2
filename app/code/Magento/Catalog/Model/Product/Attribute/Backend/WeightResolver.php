<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Attribute\Backend;

use Magento\Catalog\Api\Data\ProductAttributeInterface;

class WeightResolver
{
    /**
     * @var \Magento\Catalog\Model\Product\TypeTransitionManager
     */
    private $productTypeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * WeightResolver constructor.
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager
    ) {
        $this->productRepository = $productRepository;
        $this->productTypeManager = $productTypeManager;
    }

    /**
     * @param array $productIds
     * @param string $hasWeightValue
     * @param mixed null|string $storeId
     */
    public function resolve(array $productIds, $hasWeightValue, $storeId = null)
    {
        foreach ($productIds as $productId) {
            $product = $this->productRepository->getById($productId, false, $storeId); 
            $product->setData(
                ProductAttributeInterface::CODE_HAS_WEIGHT,
                $hasWeightValue
            );
            $this->productTypeManager->processProduct($product);
            $this->productRepository->save($product);
        }
    }
}
