<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Url\Validator */
    protected $object;

    /** @var \Zend\Validator\Uri */
    protected $zendValidator;

    /** @var string[] */
    protected $expectedValidationMessages = ['invalidUrl' => "Invalid URL '%value%'."];

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->zendValidator = $this->createMock(\Zend\Validator\Uri::class);
        $this->object = $objectManager->getObject(
            \Magento\Framework\Url\Validator::class,
            ['validator' => $this->zendValidator]
        );
    }

    public function testConstruct()
    {
        $this->assertEquals($this->expectedValidationMessages, $this->object->getMessageTemplates());
    }

    public function testIsValidWhenValid()
    {
        $this->zendValidator
            ->method('isValid')
            ->with('http://example.com')
            ->willReturn(true);

        $this->assertTrue($this->object->isValid('http://example.com'));
        $this->assertEquals([], $this->object->getMessages());
    }

    public function testIsValidWhenInvalid()
    {
        $this->zendValidator
            ->method('isValid')
            ->with('%value%')
            ->willReturn(false);

        $this->assertFalse($this->object->isValid('%value%'));
        $this->assertEquals($this->expectedValidationMessages, $this->object->getMessages());
    }
}
