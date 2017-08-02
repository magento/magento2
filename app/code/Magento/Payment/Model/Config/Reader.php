<?php
/**
 * Payment config reader
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Config;

/**
 * Class \Magento\Payment\Model\Config\Reader
 *
 */
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
