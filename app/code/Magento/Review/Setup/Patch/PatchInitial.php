<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Setup\Patch;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class PatchInitial implements \Magento\Setup\Model\Patch\DataPatchInterface
{


    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function apply(ModuleDataSetupInterface $setup)
    {
        $installer = $setup;

        //Fill table review/review_entity
        $reviewEntityCodes = [
            \Magento\Review\Model\Review::ENTITY_PRODUCT_CODE,
            \Magento\Review\Model\Review::ENTITY_CUSTOMER_CODE,
            \Magento\Review\Model\Review::ENTITY_CATEGORY_CODE,
        ];
        foreach ($reviewEntityCodes as $entityCode) {
            $installer->getConnection()->insert($installer->getTable('review_entity'), ['entity_code' => $entityCode]);
        }
        //Fill table review/review_entity
        $reviewStatuses = [
            \Magento\Review\Model\Review::STATUS_APPROVED => 'Approved',
            \Magento\Review\Model\Review::STATUS_PENDING => 'Pending',
            \Magento\Review\Model\Review::STATUS_NOT_APPROVED => 'Not Approved',
        ];
        foreach ($reviewStatuses as $k => $v) {
            $bind = ['status_id' => $k, 'status_code' => $v];
            $installer->getConnection()->insertForce($installer->getTable('review_status'), $bind);
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
            $installer->getConnection()->insert($installer->getTable('rating_entity'), ['entity_code' => $entityCode]);
            $entityId = $installer->getConnection()->lastInsertId($installer->getTable('rating_entity'));
            foreach ($ratings as $bind) {
                //Fill table rating/rating
                $bind['entity_id'] = $entityId;
                $installer->getConnection()->insert($installer->getTable('rating'), $bind);
                //Fill table rating/rating_option
                $ratingId = $installer->getConnection()->lastInsertId($installer->getTable('rating'));
                $optionData = [];
                for ($i = 1; $i <= 5; $i++) {
                    $optionData[] = ['rating_id' => $ratingId, 'code' => (string)$i, 'value' => $i, 'position' => $i];
                }
                $installer->getConnection()->insertMultiple($installer->getTable('rating_option'), $optionData);
            }
        }

    }

    /**
     * Do Revert
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function revert(ModuleDataSetupInterface $setup)
    {
    }

    /**
     * @inheritdoc
     */
    public function isDisabled()
    {
        return false;
    }


}
