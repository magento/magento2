<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Weee\Plugin\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\DataObject;
use Magento\Weee\Model\Tax;

class ConfigurableVariationAttributePriority
{
    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var Configurable
     */
    private Configurable $configurable;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param Configurable $configurable
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        Configurable $configurable
    ) {
        $this->productRepository = $productRepository;
        $this->configurable = $configurable;
    }

    /**
     * Apply parent weee attribute for variation w/o weee attribute
     *
     * @param Tax $subject
     * @param array $result
     * @param ProductInterface $product
     * @param DataObject $shipping
     * @param DataObject $billing
     * @param string $website
     * @param bool $calculateTax
     * @param bool $round
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetProductWeeeAttributes(
        Tax $subject,
        array $result,
        ProductInterface $product,
        $shipping = null,
        $billing = null,
        $website = null,
        $calculateTax = null,
        $round = true
    ):array {
        if (empty($result)) {
            foreach ($this->configurable->getParentIdsByChild($product->getId()) as $parentId) {
                $result = $subject->getProductWeeeAttributes(
                    $this->productRepository->getById($parentId),
                    $shipping,
                    $billing,
                    $website,
                    $calculateTax,
                    $round
                );
            }
        }

        return $result;
    }
}
