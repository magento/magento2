<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CompositeCollectionProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

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
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @param Visibility $catalogProductVisibility
     * @param SearchCriteriaInterface $searchCriteria
     * @param CompositeCollectionProcessor $collectionProcessor
     * @param CategoryRepository|null $categoryRepository
     */
    public function __construct(
        Visibility $catalogProductVisibility,
        SearchCriteriaInterface $searchCriteria,
        CompositeCollectionProcessor $collectionProcessor,
        ?CategoryRepository $categoryRepository = null
    ) {
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->searchCriteria = $searchCriteria;
        $this->collectionProcessor = $collectionProcessor;
        $this->categoryRepository = $categoryRepository ?? ObjectManager::getInstance()->get(CategoryRepository::class);
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
        $isAnchored = $category->getIsAnchor();
        if (!empty($value['id']) && !$isAnchored) {
            $isAnchored = $this->categoryRepository->get($value['id'])->getIsAnchor();
        }
        $category->setIsAnchor($isAnchored);
        $productsCollection = $category->getProductCollection();
        $productsCollection->setVisibility($this->catalogProductVisibility->getVisibleInSiteIds());
        $productsCollection = $this->collectionProcessor->process(
            $productsCollection,
            $this->searchCriteria,
            [],
            $context
        );

        return $productsCollection->getSize();
    }
}
