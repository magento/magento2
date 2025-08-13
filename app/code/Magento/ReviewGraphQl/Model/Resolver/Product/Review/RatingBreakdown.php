<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReviewGraphQl\Model\Resolver\Product\Review;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Review\Model\Review;
use Magento\ReviewGraphQl\Model\DataProvider\ReviewRatingsDataProvider;

/**
 * Review rating resolver
 */
class RatingBreakdown implements ResolverInterface
{
    /**
     * @var ReviewRatingsDataProvider
     */
    private $reviewRatingsDataProvider;

    /**
     * @param ReviewRatingsDataProvider $reviewRatingsDataProvider
     */
    public function __construct(
        ReviewRatingsDataProvider $reviewRatingsDataProvider
    ) {
        $this->reviewRatingsDataProvider = $reviewRatingsDataProvider;
    }

    /**
     * Resolves the rating breakdown
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
        ?array $value = null,
        ?array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new GraphQlInputException(__('Value must contain "model" property.'));
        }

        /** @var Review $review */
        $review = $value['model'];

        return $this->reviewRatingsDataProvider->getData((int) $review->getId());
    }
}
