<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

use Magento\TestFramework\Helper\ObjectManager;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Url\Validator */
    protected $object;

    /** @var string[] */
    protected $expectedValidationMessages = ['invalidUrl' => "Invalid URL '%value%'."];

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->object = $objectManager->getObject('Magento\Framework\Url\Validator');
    }

    public function testConstruct()
    {
        $this->assertEquals($this->expectedValidationMessages, $this->object->getMessageTemplates());
    }

    public function testIsValidWhenValid()
    {
        $this->assertEquals(true, $this->object->isValid('http://example.com'));
        $this->assertEquals([], $this->object->getMessages());
    }

    public function testIsValidWhenInvalid()
    {
        $this->assertEquals(false, $this->object->isValid('%value%'));
        $this->assertEquals($this->expectedValidationMessages, $this->object->getMessages());
    }
}
