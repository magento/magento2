<?php
/**
 * Integration test for Magento\Framework\Session\Config\Validator\CookieDomainValidator
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session\Config\Validator;

class CookieDomainValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Framework\Session\Config\Validator\CookieDomainValidator   */
    private $model;

    public function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $objectManager->create('Magento\Framework\Session\Config\Validator\CookieDomainValidator');
    }

    public function testEmptyString()
    {
        $this->assertTrue($this->model->isValid(''));
    }

    public function testInvalidHostname()
    {
        $this->assertFalse($this->model->isValid('http://'));
    }

    public function testNotString()
    {
        $this->assertFalse($this->model->isValid(1));
    }

    public function testNonemptyValid()
    {
        $this->assertTrue($this->model->isValid('domain.com'));
    }
}
