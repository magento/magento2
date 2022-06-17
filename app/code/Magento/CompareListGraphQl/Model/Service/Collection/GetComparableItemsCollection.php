<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Service\Collection;

use Magento\Catalog\Helper\Product\Compare;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product\Visibility as CatalogProductVisibility;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory as CompareItemsCollectionFactory;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;

/**
 * Get collection with comparable items
 */
class GetComparableItemsCollection
{
    /**
     * @var Collection
     */
    private $items;

    /**
     * @var CompareItemsCollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * @var CatalogProductVisibility
     */
    private $catalogProductVisibility;

    /**
     * @var CatalogConfig
     */
    private $catalogConfig;

    /**
     * @var Compare
     */
    private $compareProduct;

    /**
     * @param CompareItemsCollectionFactory $itemCollectionFactory
     * @param CatalogProductVisibility $catalogProductVisibility
     * @param CatalogConfig $catalogConfig
     * @param Compare $compareHelper
     */
    public function __construct(
        CompareItemsCollectionFactory $itemCollectionFactory,
        CatalogProductVisibility $catalogProductVisibility,
        CatalogConfig $catalogConfig,
        Compare $compareHelper
    ) {
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->catalogConfig = $catalogConfig;
        $this->compareProduct = $compareHelper;
    }

    /**
     * Get collection of comparable items
     *
     * @param int $listId
     * @param ContextInterface $context
     *
     * @return Collection
     */
    public function execute(int $listId, ContextInterface $context): Collection
    {
        $this->compareProduct->setAllowUsedFlat(false);
        $this->items = $this->itemCollectionFactory->create();
        $this->items->setListId($listId);
        $this->items->useProductItem()->setStoreId($context->getExtensionAttributes()->getStore()->getStoreId());
        $this->items->addAttributeToSelect(
            $this->catalogConfig->getProductAttributes()
        )->loadComparableAttributes()->addMinimalPrice()->addTaxPercents();

        return $this->items;
    }
}
