<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
namespace Magento\SomeModule\Model\Two;

require_once __DIR__ . '/../One/TestOne.php';
require_once __DIR__ . '/../Proxy.php';
class TestTwo extends \Magento\SomeModule\Model\One\TestOne
{
    /**
     * @var \Magento\SomeModule\Model\Proxy
     */
    protected $_proxy;

    /**
     * Test constructor.
     * @param \Magento\SomeModule\Model\Proxy $proxy
     * @param array $data
     */
    public function __construct(\Magento\SomeModule\Model\Proxy $proxy, $data = [])
    {
        $this->_proxy = $proxy;
    }
}
