<?php
/**
 * Integration test for Magento\Framework\ValidatorFactory
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework;

class ValidatorFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var  \Magento\Framework\ValidatorFactory */
    private $model;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $objectManager->create(\Magento\Framework\ValidatorFactory::class);
    }

    public function testCreateWithInstanceName()
    {
        $setName = \Magento\Framework\DataObject::class;
        $this->assertInstanceOf($setName, $this->model->create([], $setName));
    }

    public function testCreateDefault()
    {
        $default = \Magento\Framework\Validator::class;
        $this->assertInstanceOf($default, $this->model->create());
    }
}
