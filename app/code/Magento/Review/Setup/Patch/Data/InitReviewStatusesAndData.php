<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class InitReviewStatusesAndData implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * PatchInitial constructor.
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
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
            $this->moduleDataSetup->getConnection()->insert(
                $this->moduleDataSetup->getTable('review_entity'),
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
            $this->moduleDataSetup->getConnection()->insertForce(
                $this->moduleDataSetup->getTable('review_status'),
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
            $this->moduleDataSetup->getConnection()->insert(
                $this->moduleDataSetup->getTable('rating_entity'),
                ['entity_code' => $entityCode]
            );
            $entityId = $this->moduleDataSetup->getConnection()->lastInsertId(
                $this->moduleDataSetup->getTable('rating_entity')
            );
            foreach ($ratings as $bind) {
                //Fill table rating/rating
                $bind['entity_id'] = $entityId;
                $this->moduleDataSetup->getConnection()->insert(
                    $this->moduleDataSetup->getTable('rating'),
                    $bind
                );
                //Fill table rating/rating_option
                $ratingId = $this->moduleDataSetup->getConnection()->lastInsertId(
                    $this->moduleDataSetup->getTable('rating')
                );
                $optionData = [];
                for ($i = 1; $i <= 5; $i++) {
                    $optionData[] = ['rating_id' => $ratingId, 'code' => (string)$i, 'value' => $i, 'position' => $i];
                }
                $this->moduleDataSetup->getConnection()->insertMultiple(
                    $this->moduleDataSetup->getTable('rating_option'),
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
    public static function getVersion()
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
