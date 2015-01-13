<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SomeModule\Helper;

/**
 * @SuppressWarnings(PHPMD.ConstructorWithNameAsEnclosingClass)
 */
class Test
{
    /**
     * @var \Magento\SomeModule\ElementFactory\Proxy
     */
    protected $_factory;

    /**
     * @var \Magento\SomeModule\Element\Factory
     */
    protected $_elementFactory;

    /**
     * @var \Magento\SomeModule\ElementFactory
     */
    protected $_newElementFactory;

    public function __construct(
        \Magento\SomeModule\Module\Factory $factory,
        \Magento\SomeModule\Element\Factory $elementFactory,
        \Magento\SomeModule\ElementFactory $rightElementFactory
    ) {
        $this->_factory = $factory;
        $this->_elementFactory = $elementFactory;
        $this->_newElementFactory = $rightElementFactory;
    }

    /**
     * @param \Magento\SomeModule\ElementFactory $factory
     * @param array $data
     */
    public function testHelper(\Magento\SomeModule\ElementFactory $factory, array $data = [])
    {
        $factory->create('Magento\SomeModule\ElementFactory', ['data' => $data]);
    }
}
