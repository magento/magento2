<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductMutexException;
use Magento\Catalog\Model\ProductMutexInterface;
use Magento\Framework\Exception\CouldNotSaveException;

class ProductRepositorySaveOperationSynchronizer
{
    /**
     * @param ProductMutexInterface $productMutex
     */
    public function __construct(
        private readonly ProductMutexInterface $productMutex
    ) {
    }

    /**
     * Synchronizes product save operations to avoid data corruption from concurrent requests.
     *
     * @param ProductRepositoryInterface $subject
     * @param callable $proceed
     * @param Product $product
     * @param mixed $saveOptions
     * @return ProductInterface
     * @throws CouldNotSaveException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        ProductRepositoryInterface $subject,
        callable $proceed,
        ProductInterface $product,
        mixed $saveOptions = false
    ): ProductInterface {
        try {
            return $this->productMutex->execute((string) $product->getSku(), $proceed, $product, $saveOptions);
        } catch (ProductMutexException $e) {
            throw new CouldNotSaveException(
                __('The product was unable to be saved. Please try again.'),
                $e
            );
        }
    }
}
