<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Resolver;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\GraphQl\Model\Resolver\Products\Product;
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
     * @var Product
     */
    private $productResolver;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param Builder $searchCriteriaBuilder
     * @param Product $productResolver
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        Builder $searchCriteriaBuilder,
        Product $productResolver
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productResolver = $productResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(array $args)
    {
        if (isset($args['sku']) && !empty($args['sku']->getValue())) {
            $product = $this->productResolver->getProduct($args['sku']->getValue());
            return [
                'total_count' => 1,
                'items' => [$product],
                'page_info' => ['page_size' => 1, 'current_page' => 1]
            ];
        } else {
            unset($args['sku']);
        }
        $searchCriteria = $this->searchCriteriaBuilder->build($args);
        $itemsResults = $this->productRepository->getList($searchCriteria);

        $items = $itemsResults->getItems();

        $products = [];
        foreach ($items as $item) {
            $products[] = $this->productResolver->getProduct($item->getSku());
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
            'items' => $products,
            'page_info' => [
                'page_size' => $searchCriteria->getPageSize(),
                'current_page' => $searchCriteria->getCurrentPage()
            ]
        ];
    }
}
