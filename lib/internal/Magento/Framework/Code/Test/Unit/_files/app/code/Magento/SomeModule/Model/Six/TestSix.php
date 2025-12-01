<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
namespace Magento\SomeModule\Model\Six;

require_once __DIR__ . '/../One/TestOne.php';
require_once __DIR__ . '/../Proxy.php';
require_once __DIR__ . '/../ElementFactory.php';
class TestSix extends \Magento\SomeModule\Model\One\TestOne
{
    /**
     * @var \Magento\SomeModule\Model\ElementFactory
     */
    protected $_factory;

    /**
     * Test constructor.
     * @param \Magento\SomeModule\Model\Proxy $proxy
     * @param \Magento\SomeModule\Model\ElementFactory $factory
     */
    public function __construct(
        \Magento\SomeModule\Model\Proxy $proxy,
        \Magento\SomeModule\Model\ElementFactory $factory
    ) {
        $this->_factory = $factory;
        parent::__construct($factory);
    }
}
