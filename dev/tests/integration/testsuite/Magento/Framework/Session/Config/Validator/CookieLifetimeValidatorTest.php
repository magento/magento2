<?php
/**
 * Integration test for  Magento\Framework\Session\Config\Validator\CookieLifetimeValidator
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Session\Config\Validator;

class CookieLifetimeValidatorTest extends \PHPUnit\Framework\TestCase
{
    /** @var  \Magento\Framework\Session\Config\Validator\CookieLifetimeValidator   */
    private $model;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $objectManager->create(
            \Magento\Framework\Session\Config\Validator\CookieLifetimeValidator::class
        );
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
