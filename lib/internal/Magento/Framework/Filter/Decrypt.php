<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

/**
 * Decrypt filter
 * @since 2.0.0
 */
class Decrypt extends \Zend_Filter_Decrypt
{
    /**
     * @param \Magento\Framework\Filter\Encrypt\AdapterInterface $adapter
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\Filter\Encrypt\AdapterInterface $adapter)
    {
        $this->setAdapter($adapter);
    }
}
