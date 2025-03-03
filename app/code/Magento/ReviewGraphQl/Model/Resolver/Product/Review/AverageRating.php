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
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\Review;

/**
 * Review average rating resolver
 */
class AverageRating implements ResolverInterface
{
    /**
     * @var RatingFactory
     */
    private $ratingFactory;

    /**
     * @param RatingFactory $ratingFactory
     */
    public function __construct(
        RatingFactory $ratingFactory
    ) {
        $this->ratingFactory = $ratingFactory;
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
        ?array $value = null,
        ?array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new GraphQlInputException(__('Value must contain "model" property.'));
        }

        /** @var Review $review */
        $review = $value['model'];
        $summary = $this->ratingFactory->create()->getReviewSummary($review->getId());
        $averageRating = $summary->getSum() ?: 0;

        if ($averageRating > 0) {
            $averageRating = (float) number_format(
                (int) $summary->getSum() / (int) $summary->getCount(),
                2
            );
        }

        return $averageRating;
    }
}
