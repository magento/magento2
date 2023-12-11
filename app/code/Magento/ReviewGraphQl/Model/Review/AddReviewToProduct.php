<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ReviewGraphQl\Model\Review;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Review\Model\Rating;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\ResourceModel\Rating\Option\Vote\Collection as OptionVoteCollection;
use Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory as OptionVoteCollectionFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;

/**
 * Adding a review to specific product
 */
class AddReviewToProduct
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var RatingFactory
     */
    private $ratingFactory;

    /**
     * @var ReviewFactory
     */
    private $reviewFactory;

    /**
     * @var OptionVoteCollectionFactory
     */
    private $ratingOptionCollectionFactory;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ReviewFactory $reviewFactory
     * @param RatingFactory $ratingFactory
     * @param OptionVoteCollectionFactory $ratingOptionCollectionFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ReviewFactory $reviewFactory,
        RatingFactory $ratingFactory,
        OptionVoteCollectionFactory $ratingOptionCollectionFactory
    ) {
        $this->productRepository = $productRepository;
        $this->reviewFactory = $reviewFactory;
        $this->ratingFactory = $ratingFactory;
        $this->ratingOptionCollectionFactory = $ratingOptionCollectionFactory;
    }

    /**
     * Add review to product
     *
     * @param array $data
     * @param array $ratings
     * @param string $sku
     * @param int|null $customerId
     * @param int $storeId
     *
     * @return Review
     *
     * @throws GraphQlNoSuchEntityException
     */
    public function execute(array $data, array $ratings, string $sku, ?int $customerId, int $storeId): Review
    {
        $review = $this->reviewFactory->create()->setData($data);
        $review->unsetData('review_id');
        $productId = $this->getProductIdBySku($sku);
        $review->setEntityId($review->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE))
            ->setEntityPkValue($productId)
            ->setStatusId(Review::STATUS_PENDING)
            ->setCustomerId($customerId)
            ->setStoreId($storeId)
            ->setStores([$storeId])
            ->save();
        $this->addReviewRatingVotes($ratings, (int) $review->getId(), $customerId, $productId);
        $review->aggregate();
        $votesCollection = $this->getReviewRatingVotes((int) $review->getId(), $storeId);
        $review->setData('rating_votes', $votesCollection);
        $review->setData('sku', $sku);

        return $review;
    }

    /**
     * Get Product ID
     *
     * @param string $sku
     *
     * @return int|null
     *
     * @throws GraphQlNoSuchEntityException
     */
    private function getProductIdBySku(string $sku): ?int
    {
        try {
            $product = $this->productRepository->get($sku, false, null, true);

            return (int) $product->getId();
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__('Could not find a product with SKU "%sku"', ['sku' => $sku]));
        }
    }

    /**
     * Add review rating votes
     *
     * @param array $ratings
     * @param int $reviewId
     * @param int|null $customerId
     * @param int $productId
     *
     * @return void
     *
     * @phpcs:disable Magento2.Functions.DiscouragedFunction
     */
    private function addReviewRatingVotes(array $ratings, int $reviewId, ?int $customerId, int $productId): void
    {
        foreach ($ratings as $option) {
            $ratingId = $option['id'];
            $optionId = $option['value_id'];
            /** @var Rating $ratingModel */
            $ratingModel = $this->ratingFactory->create();
            $ratingModel->setRatingId(base64_decode($ratingId))
                ->setReviewId($reviewId)
                ->setCustomerId($customerId)
                ->addOptionVote(base64_decode($optionId), $productId);
        }
    }

    /**
     * Get review rating votes
     *
     * @param int $reviewId
     * @param int $storeId
     *
     * @return OptionVoteCollection
     */
    private function getReviewRatingVotes(int $reviewId, int $storeId): OptionVoteCollection
    {
        /** @var OptionVoteCollection $votesCollection */
        $votesCollection = $this->ratingOptionCollectionFactory->create();
        $votesCollection->setReviewFilter($reviewId)->setStoreFilter($storeId)->addRatingInfo($storeId);

        return $votesCollection;
    }
}
