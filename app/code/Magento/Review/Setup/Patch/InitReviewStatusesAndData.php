<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Setup\Patch;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

class InitReviewStatusesAndData implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * PatchInitial constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        //Fill table review/review_entity
        $reviewEntityCodes = [
            \Magento\Review\Model\Review::ENTITY_PRODUCT_CODE,
            \Magento\Review\Model\Review::ENTITY_CUSTOMER_CODE,
            \Magento\Review\Model\Review::ENTITY_CATEGORY_CODE,
        ];
        foreach ($reviewEntityCodes as $entityCode) {
            $this->resourceConnection->getConnection()->insert(
                $this->resourceConnection->getConnection()->getTableName('review_entity'),
                ['entity_code' => $entityCode]
            );
        }
        //Fill table review/review_entity
        $reviewStatuses = [
            \Magento\Review\Model\Review::STATUS_APPROVED => 'Approved',
            \Magento\Review\Model\Review::STATUS_PENDING => 'Pending',
            \Magento\Review\Model\Review::STATUS_NOT_APPROVED => 'Not Approved',
        ];
        foreach ($reviewStatuses as $k => $v) {
            $bind = ['status_id' => $k, 'status_code' => $v];
            $this->resourceConnection->getConnection()->insertForce(
                $this->resourceConnection->getConnection()->getTableName('review_status'),
                $bind
            );
        }
        $data = [
            \Magento\Review\Model\Rating::ENTITY_PRODUCT_CODE => [
                ['rating_code' => 'Quality', 'position' => 0],
                ['rating_code' => 'Value', 'position' => 0],
                ['rating_code' => 'Price', 'position' => 0],
            ],
            \Magento\Review\Model\Rating::ENTITY_PRODUCT_REVIEW_CODE => [],
            \Magento\Review\Model\Rating::ENTITY_REVIEW_CODE => [],
        ];
        foreach ($data as $entityCode => $ratings) {
            //Fill table rating/rating_entity
            $this->resourceConnection->getConnection()->insert(
                $this->resourceConnection->getConnection()->getTableName('rating_entity'),
                ['entity_code' => $entityCode]
            );
            $entityId = $this->resourceConnection->getConnection()->lastInsertId(
                $this->resourceConnection->getConnection()->getTableName('rating_entity')
            );
            foreach ($ratings as $bind) {
                //Fill table rating/rating
                $bind['entity_id'] = $entityId;
                $this->resourceConnection->getConnection()->insert(
                    $this->resourceConnection->getConnection()->getTableName('rating'),
                    $bind
                );
                //Fill table rating/rating_option
                $ratingId = $this->resourceConnection->getConnection()->lastInsertId(
                    $this->resourceConnection->getConnection()->getTableName('rating')
                );
                $optionData = [];
                for ($i = 1; $i <= 5; $i++) {
                    $optionData[] = ['rating_id' => $ratingId, 'code' => (string)$i, 'value' => $i, 'position' => $i];
                }
                $this->resourceConnection->getConnection()->insertMultiple(
                    $this->resourceConnection->getConnection()->getTableName('rating_option'),
                    $optionData
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
