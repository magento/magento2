<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

use Laminas\Filter\Encrypt as LaminasEncrypt;
use Magento\Framework\Filter\Encrypt\AdapterInterface;

/**
 * Encrypt filter
 */
class Encrypt extends LaminasEncrypt
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
