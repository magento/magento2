<?php
/**
 * Payment config reader
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Config;

class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * List of identifier attributes for merging
     *
     * @var array
     */
    protected $_idAttributes = [
        '/payment/credit_cards/type' => 'id',
        '/payment/groups/group' => 'id',
        '/payment/methods/method' => 'name',
    ];
}
