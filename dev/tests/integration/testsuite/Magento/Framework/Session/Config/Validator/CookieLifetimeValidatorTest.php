<?php
/**
 * Integration test for  Magento\Framework\Session\Config\Validator\CookieLifetimeValidator
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session\Config\Validator;

class CookieLifetimeValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Framework\Session\Config\Validator\CookieLifetimeValidator   */
    private $model;

    public function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $objectManager->create('Magento\Framework\Session\Config\Validator\CookieLifetimeValidator');
    }

    public function testNonNumeric()
    {
        $this->assertFalse($this->model->isValid('non-numeric value'));
    }

    public function testNegative()
    {
        $this->assertFalse($this->model->isValid(-1));
    }

    public function testPositive()
    {
        $this->assertTrue($this->model->isValid(1));
    }

    public function testZero()
    {
        $this->assertTrue($this->model->isValid(0));
    }
}
