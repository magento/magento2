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
 * @category    Magento
 * @package     Magento_Review
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Review data install
 *
 * @category    Magento
 * @package     Magento_Review
 * @author      Magento Core Team <core@magentocommerce.com>
 */

/* @var $installer \Magento\Core\Model\Resource\Setup */
$installer = $this;

//Fill table review/review_entity
$reviewEntityCodes = array(
    \Magento\Review\Model\Review::ENTITY_PRODUCT_CODE,
    \Magento\Review\Model\Review::ENTITY_CUSTOMER_CODE,
    \Magento\Review\Model\Review::ENTITY_CATEGORY_CODE,
);
foreach ($reviewEntityCodes as $entityCode) {
    $installer->getConnection()
            ->insert($installer->getTable('review_entity'), array('entity_code' => $entityCode));
}

//Fill table review/review_entity
$reviewStatuses = array(
    \Magento\Review\Model\Review::STATUS_APPROVED       => 'Approved',
    \Magento\Review\Model\Review::STATUS_PENDING        => 'Pending',
    \Magento\Review\Model\Review::STATUS_NOT_APPROVED   => 'Not Approved'
);
foreach ($reviewStatuses as $k => $v) {
    $bind = array(
        'status_id'     => $k,
        'status_code'   => $v
    );
    $installer->getConnection()->insertForce($installer->getTable('review_status'), $bind);
}
