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

require __DIR__ . '/../../../Magento/Catalog/_files/product_simple.php';

$review = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Review\Model\Review',
    array('data' => array('nickname' => 'Nickname', 'title' => 'Review Summary', 'detail' => 'Review text'))
);
$review->setEntityId(
    $review->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE)
)->setEntityPkValue(
    1
)->setStatusId(
    \Magento\Review\Model\Review::STATUS_PENDING
)->setStoreId(
    \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
        'Magento\Framework\StoreManagerInterface'
    )->getStore()->getId()
)->setStores(
    array(
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\StoreManagerInterface'
        )->getStore()->getId()
    )
)->save();

$review = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Review\Model\Review',
    array('data' => array('nickname' => 'Nickname', 'title' => '2 filter first review', 'detail' => 'Review text'))
);
$review->setEntityId(
    $review->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE)
)->setEntityPkValue(
    1
)->setStatusId(
    \Magento\Review\Model\Review::STATUS_APPROVED
)->setStoreId(
    \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
        'Magento\Framework\StoreManagerInterface'
    )->getStore()->getId()
)->setStores(
    array(
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\StoreManagerInterface'
        )->getStore()->getId()
    )
)->save();

$review = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Review\Model\Review',
    array('data' => array('nickname' => 'Nickname', 'title' => '1 filter second review', 'detail' => 'Review text'))
);
$review->setEntityId(
    $review->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE)
)->setEntityPkValue(
    1
)->setStatusId(
    \Magento\Review\Model\Review::STATUS_APPROVED
)->setStoreId(
    \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
        'Magento\Framework\StoreManagerInterface'
    )->getStore()->getId()
)->setStores(
    array(
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\StoreManagerInterface'
        )->getStore()->getId()
    )
)->save();
