<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SomeModule\Model\One;

require_once __DIR__ . '/../Proxy.php';
class Test
{
    /**
     * @var \Magento\SomeModule\Model\Proxy
     */
    protected $_proxy;

    public function __construct(\Magento\SomeModule\Model\Proxy $proxy)
    {
        $this->_proxy = $proxy;
    }
}
