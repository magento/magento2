<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SomeModule\Model\Three;

require_once __DIR__ . '/../Two/Test.php';
require_once __DIR__ . '/../ElementFactory.php';
require_once __DIR__ . '/../Proxy.php';
class Test extends \Magento\SomeModule\Model\Two\Test
{
    /**
     * @var \Magento\SomeModule\Model\ElementFactory
     */
    protected $_factory;

    /**
     * @var \Magento\SomeModule\Model\Proxy
     */
    protected $_proxy;

    public function __construct(
        \Magento\SomeModule\Model\Proxy $proxy,
        \Magento\SomeModule\Model\ElementFactory $factory
    ) {
        $this->_factory = $factory;
        parent::__construct($proxy);
    }
}
