<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\ResourceModel\Rating as RatingResourceModel;
use Magento\Review\Model\Review as ReviewModel;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

/**
 * Creates an active product rating with optional store-specific titles
 *
 * Titles are passed as a list of ['store_id' => ..., 'value' => ...] so store ids can be fixture references.
 */
class Rating implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'entity_code' => ReviewModel::ENTITY_PRODUCT_CODE,
        'rating_code' => 'Rating%uniqid%',
        'position' => 0,
        'is_active' => true,
        'stores' => [1],
        'titles' => [],
    ];

    /**
     * @param RatingFactory $ratingFactory
     * @param RatingResourceModel $ratingResourceModel
     * @param ProcessorInterface $dataProcessor
     */
    public function __construct(
        private readonly RatingFactory $ratingFactory,
        private readonly RatingResourceModel $ratingResourceModel,
        private readonly ProcessorInterface $dataProcessor
    ) {
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = $this->dataProcessor->process($this, $data + self::DEFAULT_DATA);
        $data['entity_id'] = $this->ratingResourceModel->getEntityIdByCode($data['entity_code']);
        $data['rating_codes'] = array_column($data['titles'], 'value', 'store_id');
        $data['stores'][] = 0;
        unset($data['entity_code'], $data['titles']);

        $rating = $this->ratingFactory->create();
        $rating->setData($data);
        $this->ratingResourceModel->save($rating);

        return $rating;
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $this->ratingResourceModel->delete($data);
    }
}
