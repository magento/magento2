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

\Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea(
    \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
);

require __DIR__ . '/../../../Magento/Customer/_files/customer.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_simple.php';

$review = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Review\Model\Review',
    ['data' => [
        'customer_id' => $customer->getId(),
        'title' => 'Review Summary',
        'detail' => 'Review text',
        'nickname' => 'Nickname',
    ]]
);

$review
    ->setEntityId($review->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE))
    ->setEntityPkValue($product->getId())
    ->setStatusId(\Magento\Review\Model\Review::STATUS_PENDING)
    ->setStoreId(
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\StoreManagerInterface')
            ->getStore()->getId()
    )
    ->setStores([
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\StoreManagerInterface')
            ->getStore()->getId()
    ])
    ->save();

\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry')->register(
    'review_data',
    $review
);
