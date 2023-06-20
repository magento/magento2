<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

use Laminas\Filter\Decrypt as LaminasDecrypt;
use Magento\Framework\Filter\Encrypt\AdapterInterface;

/**
 * Decrypt filter
 */
class Decrypt extends LaminasDecrypt
{
    /**
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        parent::__construct();

        $this->setAdapter($adapter);
    }
}
