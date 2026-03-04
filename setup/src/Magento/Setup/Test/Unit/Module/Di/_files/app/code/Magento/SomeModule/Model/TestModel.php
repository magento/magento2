<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SomeModule\Model;

/**
 * @SuppressWarnings(PHPMD.ConstructorWithNameAsEnclosingClass)
 */
class TestModel
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
        $factory->create(\Magento\SomeModule\Model\BlockFactory::class, ['data' => $data]);
    }
}
