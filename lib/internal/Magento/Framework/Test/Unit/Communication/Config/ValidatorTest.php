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

/**
 * Unit test for \Magento\Framework\Communication\Config\Validator class
 */
class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TypeProcessor|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $typeProcessor;

    /**
     * @var MethodsMap|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $methodsMap;

    protected function setUp(): void
    {
        $this->methodsMap = $this->createMock(MethodsMap::class);

        $this->methodsMap->expects(static::any())
            ->method('getMethodsMap')
            ->will($this->throwException(new \InvalidArgumentException('message', 333)));

        $this->typeProcessor = $this->createMock(TypeProcessor::class);
        $this->typeProcessor->expects(static::any())
            ->method('isTypeSimple')
            ->willReturn(false);

        $this->typeProcessor->expects(static::any())
            ->method('isTypeSimple')
            ->willReturn(false);
    }

    /**
     */
    public function testValidateResponseSchemaType()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Response schema definition has service class with wrong annotated methods');
        $this->expectExceptionCode(333);

        /** @var Validator $validator */
        $validator = new Validator($this->typeProcessor, $this->methodsMap);
        $validator->validateResponseSchemaType('123', '123');
    }

    /**
     */
    public function testValidateRequestSchemaType()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Request schema definition has service class with wrong annotated methods');
        $this->expectExceptionCode(333);

        /** @var Validator $validator */
        $validator = new Validator($this->typeProcessor, $this->methodsMap);
        $validator->validateRequestSchemaType('123', '123');
    }
}
