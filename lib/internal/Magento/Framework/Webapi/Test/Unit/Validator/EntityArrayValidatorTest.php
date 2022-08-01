<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit\Validator;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Validator\IOLimit\IOLimitConfigProvider;
use Magento\Framework\Webapi\Validator\EntityArrayValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Verifies behavior of the entity array validator
 */
class EntityArrayValidatorTest extends TestCase
{
    /**
     * @var IOLimitConfigProvider|MockObject
     */
    private $config;

    /**
     * @var EntityArrayValidator
     */
    private $validator;

    protected function setUp(): void
    {
        $this->config = self::getMockBuilder(IOLimitConfigProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validator = new EntityArrayValidator(3, $this->config);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testAllowsDataWhenBelowLimit()
    {
        $this->config->method('isInputLimitingEnabled')
            ->willReturn(true);
        $this->validator->validateComplexArrayType("foo", [[],[],[]]);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testAllowsDataWhenBelowLimitUsingConfig()
    {
        $this->config->method('isInputLimitingEnabled')
            ->willReturn(true);
        $this->config->method('getComplexArrayItemLimit')
            ->willReturn(6);
        $this->validator->validateComplexArrayType("foo", [[],[],[],[],[]]);
    }

    public function testFailsDataWhenAboveLimit()
    {
        $this->expectException(LocalizedException::class);
        $this->expectErrorMessage('Maximum items of type "foo" is 3');
        $this->config->method('isInputLimitingEnabled')
            ->willReturn(true);
        $this->validator->validateComplexArrayType("foo", [[],[],[],[]]);
    }

    public function testFailsDataWhenAboveLimitUsingConfig()
    {
        $this->expectException(LocalizedException::class);
        $this->expectErrorMessage('Maximum items of type "foo" is 6');
        $this->config->method('isInputLimitingEnabled')
            ->willReturn(true);
        $this->config->method('getComplexArrayItemLimit')
            ->willReturn(6);
        $this->validator->validateComplexArrayType("foo", [[],[],[],[],[],[],[]]);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testAboveLimitWithDisabledLimiting()
    {
        $this->config->method('isInputLimitingEnabled')
            ->willReturn(false);
        $this->validator->validateComplexArrayType("foo", [[],[],[],[],[],[],[]]);
    }
}
