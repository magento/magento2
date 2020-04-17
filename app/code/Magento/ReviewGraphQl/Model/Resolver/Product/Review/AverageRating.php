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
use Magento\Review\Model\ResourceModel\Rating\Option\Vote\Collection as VoteCollection;
use Magento\Review\Service\GetReviewAverageRatingService;

/**
 * Review average rating resolver
 */
class AverageRating implements ResolverInterface
{
    /**
     * @var GetReviewAverageRatingService
     */
    private $getReviewAverageRatingService;

    /**
     * @param GetReviewAverageRatingService $getReviewAverageRatingService
     */
    public function __construct(
        GetReviewAverageRatingService $getReviewAverageRatingService
    ) {
        $this->getReviewAverageRatingService = $getReviewAverageRatingService;
    }

    /**
     * Resolves review average rating
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return float|Value|mixed
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
        if (!isset($value['rating_votes'])) {
            throw new GraphQlInputException(__('Value must contain "rating_votes" property.'));
        }

        /** @var VoteCollection $ratingVotes */
        $ratingVotes = $value['rating_votes'];

        return $this->getReviewAverageRatingService->execute($ratingVotes->getItems());
    }
}
