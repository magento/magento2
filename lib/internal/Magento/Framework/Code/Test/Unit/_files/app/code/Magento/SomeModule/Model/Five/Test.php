<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SomeModule\Model\Five;

require_once __DIR__ . '/../Three/Test.php';
require_once __DIR__ . '/../Proxy.php';
class Test extends \Magento\SomeModule\Model\Three\Test
{
    /**
     * @var \Magento\SomeModule\Model\Proxy
     */
    protected $_proxy;

    public function __construct(\Magento\SomeModule\Model\Proxy $proxy)
    {
        parent::__construct($proxy);
    }
}
