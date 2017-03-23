<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

/**
 * Decrypt filter
 */
class Decrypt extends \Zend_Filter_Decrypt
{
    /**
     * @param \Magento\Framework\Filter\Encrypt\AdapterInterface $adapter
     */
    public function __construct(\Magento\Framework\Filter\Encrypt\AdapterInterface $adapter)
    {
        $this->setAdapter($adapter);
    }
}
