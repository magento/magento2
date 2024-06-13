<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
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
