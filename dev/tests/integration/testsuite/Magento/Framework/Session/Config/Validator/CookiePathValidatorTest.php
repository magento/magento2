<?php
/**
 * Integration test for Magento\Framework\Session\Config\Validator\CookiePathValidator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session\Config\Validator;

class CookiePathValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Framework\Session\Config\Validator\CookiePathValidator   */
    private $model;

    public function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $objectManager->create('Magento\Framework\Session\Config\Validator\CookiePathValidator');
    }

    public function testNoLeadingSlash()
    {
        $path = 'path';
        $this->assertFalse($this->model->isValid($path));
    }

    public function testInvalidPath()
    {
        $path = '/path?query=query';
        $this->assertFalse($this->model->isValid($path));
    }

    public function testValidPath()
    {
        $path = '/';
        $this->assertTrue($this->model->isValid($path));
    }
}
