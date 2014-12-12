<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/** @var $store \Magento\Store\Model\Store */
$store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Store\Model\Store');
$store->setData(
    [
        'code' => 'test',
        'website_id' => '1',
        'group_id' => '1',
        'name' => 'Test Store',
        'sort_order' => '0',
        'is_active' => '1',
    ]
);
$store->save();
