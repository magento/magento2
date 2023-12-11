<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReviewGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Review\Model\ResourceModel\Rating\Collection as RatingCollection;
use Magento\Review\Model\ResourceModel\Rating\CollectionFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\Review\Config as ReviewsConfig;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Resolve data review rating metadata
 */
class ProductReviewRatingsMetadata implements ResolverInterface
{
    /**
     * @var CollectionFactory
     */
    private $ratingCollectionFactory;

    /**
     * @var ReviewsConfig
     */
    private $reviewsConfig;

    /**
     * @param CollectionFactory $ratingCollectionFactory
     * @param ReviewsConfig $reviewsConfig
     */
    public function __construct(CollectionFactory $ratingCollectionFactory, ReviewsConfig $reviewsConfig)
    {
        $this->ratingCollectionFactory = $ratingCollectionFactory;
        $this->reviewsConfig = $reviewsConfig;
    }

    /**
     * Resolve product review ratings
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return array[]|Value|mixed
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

        $items = [];
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();

        /** @var RatingCollection $ratingCollection */
        $ratingCollection = $this->ratingCollectionFactory->create();
        $ratingCollection->addEntityFilter(Review::ENTITY_PRODUCT_CODE)
            ->setStoreFilter($store->getId())
            ->setActiveFilter(true)
            ->setPositionOrder()
            ->addOptionToItems();

        foreach ($ratingCollection->getItems() as $item) {
            $items[] = [
                'id' => base64_encode($item->getData('rating_id')),
                'name' => $item->getData('rating_code'),
                'values' => $item->getData('options')
            ];
        }

        return ['items' => $items];
    }
}
