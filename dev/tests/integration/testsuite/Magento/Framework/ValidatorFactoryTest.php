<?php
/**
 * Integration test for Magento\Framework\ValidatorFactory
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

class ValidatorFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Framework\ValidatorFactory */
    private $model;

    public function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $objectManager->create('Magento\Framework\ValidatorFactory');
    }

    public function testCreateWithInstanceName()
    {
        $setName = 'Magento\Framework\DataObject';
        $this->assertInstanceOf($setName, $this->model->create([], $setName));
    }

    public function testCreateDefault()
    {
        $default = 'Magento\Framework\Validator';
        $this->assertInstanceOf($default, $this->model->create());
    }
}
