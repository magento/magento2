<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/** @var \Magento\Log\Model\Visitor\Online $visitorOnline */
$visitorOnline = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Log\Model\Visitor\Online');
$visitorOnline->setData([
    'visitor_type'   => 'c',
    'remote_addr'    => '10101010',
    'first_visit_at' => '2014-03-02 00:00:00',
    'last_visit_at'  => '2014-03-02 01:01:01',
    'customer_id'    => 1,
    'last_url'       => 'http://last_url',
]);
$visitorOnline->save();
