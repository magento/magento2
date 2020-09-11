<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Communication\Config;

use Magento\Framework\Communication\Config\Validator;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Reflection\TypeProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Framework\Communication\Config\Validator class
 */
class ValidatorTest extends TestCase
{
    /**
     * @var TypeProcessor|MockObject
     */
    protected $typeProcessor;

    /**
     * @var MethodsMap|MockObject
     */
    protected $methodsMap;

    protected function setUp(): void
    {
        $this->methodsMap = $this->createMock(MethodsMap::class);

        $this->methodsMap->expects(static::any())
            ->method('getMethodsMap')
            ->willThrowException(new \InvalidArgumentException('message', 333));

        $this->typeProcessor = $this->createMock(TypeProcessor::class);
        $this->typeProcessor->expects(static::any())
            ->method('isTypeSimple')
            ->willReturn(false);

        $this->typeProcessor->expects(static::any())
            ->method('isTypeSimple')
            ->willReturn(false);
    }

    public function testValidateResponseSchemaType()
    {
        $this->expectException('LogicException');
        $this->expectExceptionCode('333');
        $this->expectExceptionMessage('Response schema definition has service class with wrong annotated methods');
        /** @var Validator $validator */
        $validator = new Validator($this->typeProcessor, $this->methodsMap);
        $validator->validateResponseSchemaType('123', '123');
    }

    public function testValidateRequestSchemaType()
    {
        $this->expectException('LogicException');
        $this->expectExceptionCode('333');
        $this->expectExceptionMessage('Request schema definition has service class with wrong annotated methods');
        /** @var Validator $validator */
        $validator = new Validator($this->typeProcessor, $this->methodsMap);
        $validator->validateRequestSchemaType('123', '123');
    }
}
