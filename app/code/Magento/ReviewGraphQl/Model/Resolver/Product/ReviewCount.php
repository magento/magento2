<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReviewGraphQl\Model\Resolver\Product;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Review\Model\Review;
use Magento\Review\Model\Review\Config as ReviewsConfig;

/**
 * Product total review count
 */
class ReviewCount implements ResolverInterface
{
    /**
     * @var Review
     */
    private $review;

    /**
     * @var ReviewsConfig
     */
    private $reviewsConfig;

    /**
     * @param Review $review
     * @param ReviewsConfig $reviewsConfig
     */
    public function __construct(Review $review, ReviewsConfig $reviewsConfig)
    {
        $this->review = $review;
        $this->reviewsConfig = $reviewsConfig;
    }

    /**
     * Resolves the product total reviews
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return int|Value|mixed
     *
     * @throws GraphQlInputException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (false === $this->reviewsConfig->isEnabled()) {
            return 0;
        }

        if (!isset($value['model'])) {
            throw new GraphQlInputException(__('Value must contain "model" property.'));
        }

        /** @var Product $product */
        $product = $value['model'];

        return (int) $this->review->getTotalReviews(
            $product->getId(),
            true,
            (int) $context->getExtensionAttributes()->getStore()->getId()
        );
    }
}
