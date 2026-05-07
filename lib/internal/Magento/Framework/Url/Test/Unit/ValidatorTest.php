<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Url\Test\Unit;

use Laminas\Validator\Uri;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /** @var Validator */
    protected $object;

    /** @var Uri */
    protected $laminasValidator;

    /** @var string[] */
    protected $expectedValidationMessages = [Uri::INVALID => "Invalid URL '%value%'."];

    /** @var string[] */
    protected $invalidURL = [Uri::INVALID => "Invalid URL 'php://filter'."];

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->laminasValidator = $this->createMock(Uri::class);
        $this->object = $objectManager->getObject(
            Validator::class,
            ['validator' => $this->laminasValidator]
        );
    }

    public function testConstruct()
    {
        $this->assertEquals($this->expectedValidationMessages, $this->object->getMessageTemplates());
    }

    public function testIsValidWhenValid()
    {
        $this->laminasValidator
            ->method('isValid')
            ->with('http://example.com')
            ->willReturn(true);

        $this->assertTrue($this->object->isValid('http://example.com'));
        $this->assertEquals([], $this->object->getMessages());
    }

    public function testIsValidWhenInvalid()
    {
        $this->laminasValidator
            ->method('isValid')
            ->with('%value%')
            ->willReturn(false);
        $this->assertFalse($this->object->isValid('%value%'));
        $this->assertEquals($this->expectedValidationMessages, $this->object->getMessages());
    }

    public function testIsValidWhenInvalidURL()
    {
        $this->laminasValidator
            ->method('isValid')
            ->with('php://filter');
        $this->assertFalse($this->object->isValid('php://filter'));
        $this->assertEquals($this->invalidURL, $this->object->getMessages());
    }
}
