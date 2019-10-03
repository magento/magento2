<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Category;;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;

/**
 * Category filter allows to filter collection using 'id, url_key, name' from search criteria.
 */
class CategoryFilter
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @param CategoryRepositoryInterface $categoryRepository;
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        CollectionFactory $collectionFactory
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Filter for filtering the requested categories id's based on url_key, ids, name in the result.
     *
     * @param array $args
     *
     * @return array|int
     */
    public function applyFilters(array $args)
    {
        $categoryCollection = $this->collectionFactory->create();
        foreach($args['filters'] as $field => $cond){
            foreach($cond as $condType => $value){
                if($field === 'ids'){
                    $categoryCollection->addIdFilter($value);
                } else {
                    $categoryCollection->addAttributeToFilter($field, [$condType => $value]);
                }
            }
        }
        $categoryIds = [];
        $categoriesData = $categoryCollection->getData();
        foreach ($categoriesData as $categoryData){
             $categoryIds[] = (int)$categoryData['entity_id'];
        }
       return $categoryIds;
    }
}
