<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Review\Model\ResourceModel\Review as ReviewResourceModel;
use Magento\Review\Model\Review as ReviewModel;
use Magento\Review\Model\ReviewFactory as ReviewModelFactory;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class Review implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'entity_code' => ReviewModel::ENTITY_PRODUCT_CODE,
        'entity_pk_value' => 1,
        'nickname' => 'Nickname',
        'title' => 'Review title',
        'detail' => 'Review detail',
        'status_id' => ReviewModel::STATUS_APPROVED,
        'store_id' => 1,
    ];

    /**
     * @var ReviewModelFactory
     */
    private $reviewModelFactory;

    /**
     * @var ReviewResourceModel
     */
    private $reviewResourceModel;

    /**
     * @param ReviewModelFactory $reviewModelFactory
     * @param ReviewResourceModel $reviewResourceModel
     */
    public function __construct(
        ReviewModelFactory $reviewModelFactory,
        ReviewResourceModel $reviewResourceModel
    ) {
        $this->reviewModelFactory = $reviewModelFactory;
        $this->reviewResourceModel = $reviewResourceModel;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $data += self::DEFAULT_DATA;
        $data['entity_id'] = $this->reviewResourceModel->getEntityIdByCode($data['entity_code']);
        unset($data['entity_code']);
        $reviewModel = $this->reviewModelFactory->create(['data' => $data]);
        $reviewModel->setStores([$data['store_id']]);
        $this->reviewResourceModel->save($reviewModel);

        return $reviewModel;
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $this->reviewResourceModel->delete($data);
    }
}
