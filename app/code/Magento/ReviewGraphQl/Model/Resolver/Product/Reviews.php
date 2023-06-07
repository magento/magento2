<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReviewGraphQl\Model\Resolver\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Review\Model\Review\Config as ReviewsConfig;
use Magento\ReviewGraphQl\Model\DataProvider\AggregatedReviewsDataProvider;
use Magento\ReviewGraphQl\Model\DataProvider\ProductReviewsDataProvider;

/**
 * Product reviews resolver, used by GraphQL endpoints to retrieve product's reviews
 */
class Reviews implements ResolverInterface
{
    /**
     * @var ProductReviewsDataProvider
     */
    private $productReviewsDataProvider;

    /**
     * @var AggregatedReviewsDataProvider
     */
    private $aggregatedReviewsDataProvider;

    /**
     * @var ReviewsConfig
     */
    private $reviewsConfig;

    /**
     * @param ProductReviewsDataProvider $productReviewsDataProvider
     * @param AggregatedReviewsDataProvider $aggregatedReviewsDataProvider
     * @param ReviewsConfig $reviewsConfig
     */
    public function __construct(
        ProductReviewsDataProvider $productReviewsDataProvider,
        AggregatedReviewsDataProvider $aggregatedReviewsDataProvider,
        ReviewsConfig $reviewsConfig
    ) {
        $this->productReviewsDataProvider = $productReviewsDataProvider;
        $this->aggregatedReviewsDataProvider = $aggregatedReviewsDataProvider;
        $this->reviewsConfig = $reviewsConfig;
    }

    /**
     * Resolves the product reviews
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return array|Value|mixed
     *
     * @throws GraphQlInputException
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
        if (false === $this->reviewsConfig->isEnabled()) {
            return ['items' => []];
        }

        if (!isset($value['model'])) {
            throw new GraphQlInputException(__('Value must contain "model" property.'));
        }

        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }

        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }

        /** @var Product $product */
        $product = $value['model'];
        $reviewsCollection = $this->productReviewsDataProvider->getData(
            (int) $product->getId(),
            $args['currentPage'],
            $args['pageSize'],
            (int) $context->getExtensionAttributes()->getStore()->getId()
        );

        return $this->aggregatedReviewsDataProvider->getData($reviewsCollection);
    }
}
