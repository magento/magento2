<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\Plugin;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;

/**
 *  Extender of product identities for child of configurable products
 */
class ProductIdentitiesExtender
{
    /**
     * @var Configurable
     */
    private $configurableType;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param Configurable $configurableType
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(Configurable $configurableType, ProductRepositoryInterface $productRepository)
    {
        $this->configurableType = $configurableType;
        $this->productRepository = $productRepository;
    }

    /**
     * Add parent identities to product identities
     *
     * @param Product $subject
     * @param array $identities
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetIdentities(Product $subject, array $identities): array
    {
        $identities = (array) $identities;

        foreach ($this->configurableType->getParentIdsByChild($subject->getId()) as $parentId) {
            $parentProduct = $this->productRepository->getById($parentId);
            $identities = array_merge($identities, (array) $parentProduct->getIdentities());
        }

        return array_unique($identities);
    }
}
