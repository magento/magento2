<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
namespace Magento\SomeModule\Model\Five;

require_once __DIR__ . '/../Three/TestThree.php';
require_once __DIR__ . '/../Proxy.php';
class TestFive extends \Magento\SomeModule\Model\Three\TestThree
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
        parent::__construct($proxy);
    }
}
