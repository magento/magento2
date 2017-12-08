<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Resolver;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\GraphQl\Model\ResolverInterface;
use Magento\Framework\GraphQl\Argument\SearchCriteria\Builder;

/**
 * Products field resolver, used for GraphQL request processing.
 */
class Products implements ResolverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Builder
     */
    private $searchCriteriaBuilder;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param Builder $searchCriteriaBuilder
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        Builder $searchCriteriaBuilder
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(array $args)
    {
        $searchCriteria = $this->searchCriteriaBuilder->build($args);
        $itemsResults = $this->productRepository->getList($searchCriteria);

        $items = $itemsResults->getItems();

        foreach ($items as $item) {
            foreach ($item->getCustomAttributes() as $attribute) {
                $item->setData($attribute->getAttributeCode(), $attribute->getValue());
            }
        }

        $maxPages = ceil($itemsResults->getTotalCount() / $searchCriteria->getPageSize());
        if ($searchCriteria->getCurrentPage() > $maxPages && $itemsResults->getTotalCount() > 0) {
            throw new \Magento\Framework\GraphQl\Exception\GraphQlInputException(
                __(
                    'The value specified in the currentPage attribute is greater than the number'
                    . ' of pages available (%1).',
                    $maxPages
                )
            );
        }

        return [
            'total_count' => $itemsResults->getTotalCount(),
            'items' => $items,
            'page_info' => [
                'page_size' => $searchCriteria->getPageSize(),
                'current_page' => $searchCriteria->getCurrentPage()
            ]
        ];
    }
}
