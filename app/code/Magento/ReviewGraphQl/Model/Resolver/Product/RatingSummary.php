<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReviewGraphQl\Model\Resolver\Product;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Review\Model\Review\Config as ReviewsConfig;
use Magento\Review\Model\Review\SummaryFactory;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Average rating for the product
 */
class RatingSummary implements ResolverInterface
{
    /**
     * @var SummaryFactory
     */
    private $summaryFactory;

    /**
     * @var ReviewsConfig
     */
    private $reviewsConfig;

    /**
     * @param SummaryFactory $summaryFactory
     * @param ReviewsConfig $reviewsConfig
     */
    public function __construct(
        SummaryFactory $summaryFactory,
        ReviewsConfig $reviewsConfig
    ) {
        $this->summaryFactory = $summaryFactory;
        $this->reviewsConfig = $reviewsConfig;
    }

    /**
     * Resolves the product rating summary
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return float
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
    ): float {
        if (false === $this->reviewsConfig->isEnabled()) {
            return 0;
        }

        if (!isset($value['model'])) {
            throw new GraphQlInputException(__('Value must contain "model" property.'));
        }

        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();

        /** @var Product $product */
        $product = $value['model'];

        try {
            $summary = $this->summaryFactory->create()->setStoreId($store->getId())->load($product->getId());

            return floatval($summary->getData('rating_summary'));
        } catch (Exception $e) {
            throw new GraphQlInputException(__('Couldn\'t get the product rating summary.'));
        }
    }
}
