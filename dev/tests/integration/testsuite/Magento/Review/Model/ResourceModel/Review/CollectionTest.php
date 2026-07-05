<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Review\Model\ResourceModel\Review;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\ObjectManagerInterface;
use Magento\Review\Model\Rating;
use Magento\Review\Model\ResourceModel\Rating\Collection as RatingCollection;
use Magento\Review\Model\ResourceModel\Rating\Option\Vote\Collection as VoteCollection;
use Magento\Review\Model\Review;
use Magento\Review\Test\Fixture\Review as ReviewFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(ReviewFixture::class, ['entity_pk_value' => '$product.id$'], 'review1'),
        DataFixture(ReviewFixture::class, ['entity_pk_value' => '$product.id$'], 'review2'),
        DataFixture(ReviewFixture::class, ['entity_pk_value' => '$product.id$'], 'review3'),
    ]
    public function testAddRateVotesLoadsAllVotesWithSingleQuery(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $product = $fixtures->get('product');
        $review1 = $fixtures->get('review1');
        $review2 = $fixtures->get('review2');
        $review3 = $fixtures->get('review3');

        [$rating, $options] = $this->getRatingWithOptions();
        $this->addVote($rating, (int)$review1->getId(), (int)$options[0]->getId(), (int)$product->getId());
        $this->addVote($rating, (int)$review2->getId(), (int)end($options)->getId(), (int)$product->getId());

        /** @var Collection $collection */
        $collection = $this->objectManager->create(Collection::class);
        $collection->addStatusFilter(Review::STATUS_APPROVED)
            ->addEntityFilter('product', (int)$product->getId())
            ->load();
        $this->assertCount(3, $collection->getItems());

        $profiler = $collection->getConnection()->getProfiler();
        $profiler->setEnabled(true);
        $queriesBefore = $profiler->getTotalNumQueries();

        $collection->addRateVotes();

        $this->assertSame(
            1,
            $profiler->getTotalNumQueries() - $queriesBefore,
            'addRateVotes() must load votes for all reviews with a single query'
        );

        $expectedVoteCounts = [
            (int)$review1->getId() => 1,
            (int)$review2->getId() => 1,
            (int)$review3->getId() => 0,
        ];
        $queriesBeforeIteration = $profiler->getTotalNumQueries();
        foreach ($expectedVoteCounts as $reviewId => $expectedVotes) {
            $ratingVotes = $collection->getItemById($reviewId)->getRatingVotes();
            $this->assertInstanceOf(VoteCollection::class, $ratingVotes);
            $this->assertCount($expectedVotes, $ratingVotes);
            foreach ($ratingVotes as $vote) {
                $this->assertSame($reviewId, (int)$vote->getReviewId());
                $this->assertNotEmpty($vote->getRatingCode());
                $this->assertNotEmpty($vote->getPercent());
            }
            // second iteration must not trigger a lazy load of the hydrated collection
            foreach ($ratingVotes as $vote) {
                $this->assertSame($reviewId, (int)$vote->getReviewId());
            }
        }
        $this->assertSame(
            $queriesBeforeIteration,
            $profiler->getTotalNumQueries(),
            'Iterating rating votes must not trigger additional queries'
        );
        $profiler->setEnabled(false);
    }

    /**
     * Get a rating available for the default store together with its options
     *
     * @return array
     */
    private function getRatingWithOptions(): array
    {
        /** @var RatingCollection $ratingCollection */
        $ratingCollection = $this->objectManager->create(RatingCollection::class);
        $ratingCollection->addOptionToItems();
        foreach ($ratingCollection as $rating) {
            $options = array_values($rating->getOptions());
            if ($options) {
                $rating->setStores([0, 1])->setIsActive(1)->save();

                return [$rating, $options];
            }
        }
        $this->fail('No rating with options is available in the test environment');
    }

    /**
     * Add a rating option vote to the given review
     *
     * @param Rating $rating
     * @param int $reviewId
     * @param int $optionId
     * @param int $productId
     * @return void
     */
    private function addVote(Rating $rating, int $reviewId, int $optionId, int $productId): void
    {
        $this->objectManager->create(Rating::class)
            ->setRatingId($rating->getId())
            ->setReviewId($reviewId)
            ->addOptionVote($optionId, $productId);
    }
}
