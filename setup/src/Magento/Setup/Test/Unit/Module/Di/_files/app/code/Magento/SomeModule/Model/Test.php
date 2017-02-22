<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SomeModule\Model;

/**
 * @SuppressWarnings(PHPMD.ConstructorWithNameAsEnclosingClass)
 */
class Test
{
    public function __construct()
    {
        new \Magento\SomeModule\Model\Element\Proxy();
    }

    /**
     * @param \Magento\SomeModule\ModelFactory $factory
     * @param array $data
     */
    public function testModel(\Magento\SomeModule\ModelFactory $factory, array $data = [])
    {
        $factory->create('Magento\SomeModule\Model\BlockFactory', ['data' => $data]);
    }
}
