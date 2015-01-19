<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
