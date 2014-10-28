<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Review data install
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */

/* @var $installer \Magento\Framework\Module\Setup */
$installer = $this;

//Fill table review/review_entity
$reviewEntityCodes = array(
    \Magento\Review\Model\Review::ENTITY_PRODUCT_CODE,
    \Magento\Review\Model\Review::ENTITY_CUSTOMER_CODE,
    \Magento\Review\Model\Review::ENTITY_CATEGORY_CODE
);
foreach ($reviewEntityCodes as $entityCode) {
    $installer->getConnection()->insert($installer->getTable('review_entity'), array('entity_code' => $entityCode));
}

//Fill table review/review_entity
$reviewStatuses = array(
    \Magento\Review\Model\Review::STATUS_APPROVED => 'Approved',
    \Magento\Review\Model\Review::STATUS_PENDING => 'Pending',
    \Magento\Review\Model\Review::STATUS_NOT_APPROVED => 'Not Approved'
);
foreach ($reviewStatuses as $k => $v) {
    $bind = array('status_id' => $k, 'status_code' => $v);
    $installer->getConnection()->insertForce($installer->getTable('review_status'), $bind);
}

$data = array(
    \Magento\Review\Model\Rating::ENTITY_PRODUCT_CODE => array(
        array('rating_code' => 'Quality', 'position' => 0),
        array('rating_code' => 'Value', 'position' => 0),
        array('rating_code' => 'Price', 'position' => 0)
    ),
    \Magento\Review\Model\Rating::ENTITY_PRODUCT_REVIEW_CODE => array(),
    \Magento\Review\Model\Rating::ENTITY_REVIEW_CODE => array()
);

foreach ($data as $entityCode => $ratings) {
    //Fill table rating/rating_entity
    $installer->getConnection()->insert($installer->getTable('rating_entity'), array('entity_code' => $entityCode));
    $entityId = $installer->getConnection()->lastInsertId($installer->getTable('rating_entity'));

    foreach ($ratings as $bind) {
        //Fill table rating/rating
        $bind['entity_id'] = $entityId;
        $installer->getConnection()->insert($installer->getTable('rating'), $bind);

        //Fill table rating/rating_option
        $ratingId = $installer->getConnection()->lastInsertId($installer->getTable('rating'));
        $optionData = array();
        for ($i = 1; $i <= 5; $i++) {
            $optionData[] = array('rating_id' => $ratingId, 'code' => (string)$i, 'value' => $i, 'position' => $i);
        }
        $installer->getConnection()->insertMultiple($installer->getTable('rating_option'), $optionData);
    }
}
