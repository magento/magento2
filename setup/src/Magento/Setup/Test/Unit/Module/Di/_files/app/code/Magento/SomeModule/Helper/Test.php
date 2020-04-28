<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SomeModule\Helper;

use Magento\SomeModule\ElementFactory;

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
     * @var ElementFactory
     */
    protected $_newElementFactory;

    /**
     * Test constructor.
     * @param \Magento\SomeModule\Module\Factory $factory
     * @param \Magento\SomeModule\Element\Factory $elementFactory
     * @param ElementFactory $rightElementFactory
     */
    public function __construct(
        \Magento\SomeModule\Module\Factory $factory,
        \Magento\SomeModule\Element\Factory $elementFactory,
        ElementFactory $rightElementFactory
    ) {
        $this->_factory = $factory;
        $this->_elementFactory = $elementFactory;
        $this->_newElementFactory = $rightElementFactory;
    }

    /**
     * @param ElementFactory $factory
     * @param array $data
     */
    public function testHelper(ElementFactory $factory, array $data = [])
    {
        $factory->create(ElementFactory::class, ['data' => $data]);
    }
}
