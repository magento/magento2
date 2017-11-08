<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Resolver;

use Magento\Catalog\Api\ProductRepositoryInterface;
use GraphQL\Type\Definition\ResolveInfo;
use Magento\GraphQl\Model\ResolverInterface;
use Magento\GraphQl\Model\Resolver\Products\SearchCriteriaFactory;

/**
 * Products field resolver, used for GraphQL request processing.
 */
class Products implements ResolverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /** @var SearchCriteriaFactory */
    private $searchCriteriaFactory;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\GraphQl\Model\Resolver\Products\SearchCriteriaFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        \Magento\GraphQl\Model\Resolver\Products\SearchCriteriaFactory $searchCriteriaFactory
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaFactory = $searchCriteriaFactory;
    }

    /**
     * {@inheritdoc}
     * @throws \GraphQL\Error\Error
     */
    public function resolve(array $args, ResolveInfo $info)
    {
        $searchCriteria = $this->searchCriteriaFactory->create($info);

        $itemsResults = $this->productRepository->getList($searchCriteria);

        $items = $itemsResults->getItems();

        foreach ($items as $item) {
            foreach ($item->getCustomAttributes() as $attribute) {
                $item->setData($attribute->getAttributeCode(), $attribute->getValue());
            }
        }

        $maxPages = ceil($itemsResults->getTotalCount() / $searchCriteria->getPageSize());
        if ($searchCriteria->getCurrentPage() > $maxPages && $itemsResults->getTotalCount() > 0) {
            throw new \GraphQL\Error\Error(sprintf('Current page is bigger than maximum %s', $maxPages));
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
