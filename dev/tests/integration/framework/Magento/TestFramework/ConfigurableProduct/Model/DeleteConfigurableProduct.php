<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\ConfigurableProduct\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;

/**
 * Delete configurable product with linked products
 */
class DeleteConfigurableProduct
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ProductResource */
    private $productResource;

    /** @var Registry */
    private $registry;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ProductResource $productResource
     * @param Registry $registry
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductResource $productResource,
        Registry $registry
    ) {
        $this->productRepository = $productRepository;
        $this->productResource = $productResource;
        $this->registry = $registry;
    }

    /**
     * Delete configurable product and linked products
     *
     * @param string $sku
     * @return void
     */
    public function execute(string $sku): void
    {
        $configurableProduct = $this->productRepository->get($sku, false, null, true);
        $childrenIds = $configurableProduct->getExtensionAttributes()->getConfigurableProductLinks();
        $childrenSkus = array_column($this->productResource->getProductsSku($childrenIds), 'sku');
        $childrenSkus[] = $sku;
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        foreach ($childrenSkus as $childSku) {
            try {
                $this->productRepository->deleteById($childSku);
            } catch (NoSuchEntityException $e) {
                //product already removed
            }
        }

        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);
    }
}
