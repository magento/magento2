<?php
/**
 * Integration test for Magento\Framework\ValidatorFactory
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
        $setName = 'Magento\Framework\Object';
        $this->assertInstanceOf($setName, $this->model->create([], $setName));
    }

    public function testCreateDefault()
    {
        $default = 'Magento\Framework\Validator';
        $this->assertInstanceOf($default, $this->model->create());
    }
}
