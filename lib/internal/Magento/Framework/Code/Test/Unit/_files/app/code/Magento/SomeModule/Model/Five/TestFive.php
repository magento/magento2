<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
