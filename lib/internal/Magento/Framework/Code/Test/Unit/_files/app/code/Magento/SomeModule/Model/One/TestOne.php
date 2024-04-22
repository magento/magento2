<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SomeModule\Model\One;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Proxy.php';
class TestOne
{
    /**
     * @var \Magento\SomeModule\Model\Proxy
     */
    protected $_proxy;

    /**
     * Test constructor.
     * @param \Magento\SomeModule\Model\Proxy $proxy
     */
    public function __construct(\Magento\SomeModule\Model\Proxy $proxy)
    {
        $this->_proxy = $proxy;
    }
}
