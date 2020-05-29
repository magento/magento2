<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Resolver;

use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product\Visibility as CatalogProbuctVisibility;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory as CompareItemsCollectionFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class CompareItemsResolver implements ResolverInterface
{
    /**
     * @var CompareItemsCollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * @var CatalogProbuctVisibility
     */
    private $catalogProductVisibility;

    /**
     * @var CatalogConfig
     */
    private $catalogConfig;

    /**
     * @param CompareItemsCollectionFactory $itemCollectionFactory
     * @param CatalogProbuctVisibility      $catalogProductVisibility
     * @param CatalogConfig                 $catalogConfig
     */
    public function __construct(
        CompareItemsCollectionFactory $itemCollectionFactory,
        CatalogProbuctVisibility $catalogProductVisibility,
        CatalogConfig $catalogConfig
    ) {
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->catalogConfig = $catalogConfig;
    }

    /**
     * @param Field            $field
     * @param ContextInterface $context
     * @param ResolveInfo      $info
     * @param array|null       $value
     * @param array|null       $args
     *
     * @return array|Value|mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $items = [];

        $comparableItems = $this->getComparableItems($context);
        foreach ($comparableItems as $item) {
            $items[] = [
                'productId' => $item->getId(),
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'canonical_url' => $item->getProductUrl()
            ];
        }

        return $items;
    }

    /**
     * Get comparable items for current user
     *
     * @param $context
     *
     * @return Collection $comparableItems
     */
    private function getComparableItems($context): Collection
    {
        $comparableItems = $this->itemCollectionFactory->create();
        $comparableItems->setCustomerId($context->getUserId());
        $comparableItems->useProductItem(true);

        $comparableItems->addAttributeToSelect(
            $this->catalogConfig->getProductAttributes()
        )->loadComparableAttributes()->addMinimalPrice()->addTaxPercents()->setVisibility(
            $this->catalogProductVisibility->getVisibleInSiteIds()
        );

        return $comparableItems;
    }
}
