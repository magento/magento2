<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CompositeCollectionProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;


/**
 * Retrieves products count for a category
 */
class ProductsCount implements ResolverInterface
{
    /**
     * @var Visibility
     */
    private $catalogProductVisibility;

    /**
     * @var CompositeCollectionProcessor
     */
    private $collectionProcessor;

    /**
     * @var SearchCriteriaInterface
     */
    private $searchCriteria;

    /**
     * @param Visibility $catalogProductVisibility
     * @param SearchCriteriaInterface $searchCriteria
     * @param CompositeCollectionProcessor $collectionProcessor
     */
    public function __construct(
        Visibility $catalogProductVisibility,
        SearchCriteriaInterface $searchCriteria,
        CompositeCollectionProcessor $collectionProcessor
    ) {
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->searchCriteria = $searchCriteria;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new GraphQlInputException(__('"model" value should be specified'));
        }
        /** @var Category $category */
        $category = $value['model'];
        $productsCollection = $category->getProductCollection();
        $productsCollection->setVisibility($this->catalogProductVisibility->getVisibleInSiteIds());
        $productsCollection = $this->collectionProcessor->process(
            $productsCollection,
            $this->searchCriteria,
            [],
            $context
        );
        $size = $productsCollection->getSize();

        return $size;
    }
}
