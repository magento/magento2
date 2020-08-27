<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReviewGraphQl\Model\Resolver\Customer;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\ReviewGraphQl\Model\DataProvider\AggregatedReviewsDataProvider;
use Magento\ReviewGraphQl\Model\DataProvider\CustomerReviewsDataProvider;

/**
 * Customer reviews resolver, used by GraphQL endpoints to retrieve customer's reviews
 */
class Reviews implements ResolverInterface
{
    /**
     * @var CustomerReviewsDataProvider
     */
    private $customerReviewsDataProvider;

    /**
     * @var AggregatedReviewsDataProvider
     */
    private $aggregatedReviewsDataProvider;

    /**
     * @param CustomerReviewsDataProvider $customerReviewsDataProvider
     * @param AggregatedReviewsDataProvider $aggregatedReviewsDataProvider
     */
    public function __construct(
        CustomerReviewsDataProvider $customerReviewsDataProvider,
        AggregatedReviewsDataProvider $aggregatedReviewsDataProvider
    ) {
        $this->customerReviewsDataProvider = $customerReviewsDataProvider;
        $this->aggregatedReviewsDataProvider = $aggregatedReviewsDataProvider;
    }

    /**
     * Resolves the customer reviews
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
     * @throws GraphQlAuthorizationException
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
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }

        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }

        $reviewsCollection = $this->customerReviewsDataProvider->getData(
            (int) $context->getUserId(),
            $args['currentPage'],
            $args['pageSize']
        );

        return $this->aggregatedReviewsDataProvider->getData($reviewsCollection);
    }
}
