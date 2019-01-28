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
     * @var TypeProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeProcessor;

    /**
     * @var MethodsMap|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $methodsMap;

    public function setUp()
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
        $this->setExpectedException(\LogicException::class, 'Response schema definition has service class with wrong annotated methods', 333);

        /** @var Validator $validator */
        $validator = new Validator($this->typeProcessor, $this->methodsMap);
        $validator->validateResponseSchemaType('123', '123');
    }

    /**
     */
    public function testValidateRequestSchemaType()
    {
        $this->setExpectedException(\LogicException::class, 'Request schema definition has service class with wrong annotated methods', 333);

        /** @var Validator $validator */
        $validator = new Validator($this->typeProcessor, $this->methodsMap);
        $validator->validateRequestSchemaType('123', '123');
    }
}
