<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

use Magento\Framework\Filter\Encrypt\Basic as Adapter;

/**
 * Decrypt filter
 */
class Decrypt extends \Zend_Filter_Decrypt
{
    /**
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter)
    {
        $this->setAdapter($adapter);
    }
}
