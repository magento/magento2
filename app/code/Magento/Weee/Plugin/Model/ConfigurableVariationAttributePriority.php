<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Weee\Plugin\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Weee\Model\Tax;

class ConfigurableVariationAttributePriority implements ResetAfterRequestInterface
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
     * @var array<int, int[]>
     */
    private array $parentIdsByChild = [];

    /**
     * @var array<string, array>
     */
    private array $parentWeeeCache = [];

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
     * Apply parent weee attribute for variation w/o weee attribute.
     *
     * Optimised to:
     *  - cache parent id lookups per child id,
     *  - cache parent weee attribute lookups per parent id + scope
     *    signature so repeated calls for variants of the same
     *    configurable reuse the parent's resolved attributes, and
     *  - stop iterating once a non-empty result is found (the previous
     *    implementation kept iterating and overwriting the result on
     *    every parent, repeatedly loading parents in the process).
     *
     * @param Tax $subject
     * @param array $result
     * @param ProductInterface $product
     * @param DataObject|null $shipping
     * @param DataObject|null $billing
     * @param string|null $website
     * @param bool|null $calculateTax
     * @param bool $round
     * @return array
     * @SuppressWarnings(PHPMD.LongVariable)
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
    ): array {
        if (!empty($result)) {
            return $result;
        }

        $childId = (int)$product->getId();
        if ($childId === 0) {
            return $result;
        }

        if (!array_key_exists($childId, $this->parentIdsByChild)) {
            $this->parentIdsByChild[$childId] = $this->configurable->getParentIdsByChild($childId);
        }
        $parentIds = $this->parentIdsByChild[$childId];

        if (empty($parentIds)) {
            return $result;
        }

        $cacheSuffix = $this->buildScopeKey($shipping, $billing, $website, $calculateTax, $round);

        foreach ($parentIds as $parentId) {
            $cacheKey = $parentId . '|' . $cacheSuffix;
            if (!array_key_exists($cacheKey, $this->parentWeeeCache)) {
                $this->parentWeeeCache[$cacheKey] = $subject->getProductWeeeAttributes(
                    $this->productRepository->getById($parentId),
                    $shipping,
                    $billing,
                    $website,
                    $calculateTax,
                    $round
                );
            }
            $parentResult = $this->parentWeeeCache[$cacheKey];
            if (!empty($parentResult)) {
                return $parentResult;
            }
        }

        return $result;
    }

    /**
     * Build a stable string signature for the scope arguments so the
     * cache key collapses identical lookups across child variants.
     *
     * @param DataObject|null $shipping
     * @param DataObject|null $billing
     * @param string|null $website
     * @param bool|null $calculateTax
     * @param bool $round
     * @return string
     */
    private function buildScopeKey(
        ?DataObject $shipping,
        ?DataObject $billing,
        ?string $website,
        ?bool $calculateTax,
        bool $round
    ): string {
        return sprintf(
            '%s|%s|%s|%s|%d',
            $shipping ? spl_object_id($shipping) : '0',
            $billing ? spl_object_id($billing) : '0',
            (string)$website,
            $calculateTax === null ? 'n' : ($calculateTax ? '1' : '0'),
            $round ? 1 : 0
        );
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->parentIdsByChild = [];
        $this->parentWeeeCache = [];
    }
}
