<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Gateway\Validator;

use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Payment\Gateway\Validator\ValidatorComposite;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use PHPUnit\Framework\TestCase;

class ValidatorCompositeTest extends TestCase
{
    public function testValidate()
    {
        $validationSubject = [];
        $validator1 = $this->getMockBuilder(ValidatorInterface::class)
            ->getMockForAbstractClass();
        $validator2 = $this->getMockBuilder(ValidatorInterface::class)
            ->getMockForAbstractClass();
        $tMapFactory = $this->getMockBuilder(TMapFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $tMap = $this->getMockBuilder(TMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $tMapFactory->expects(static::once())
            ->method('create')
            ->with(
                [
                    'array' => [
                        'validator1' => ValidatorInterface::class,
                        'validator2' => ValidatorInterface::class
                    ],
                    'type' => ValidatorInterface::class
                ]
            )
            ->willReturn($tMap);
        $tMap->expects(static::once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$validator1, $validator2]));

        $resultSuccess = $this->getMockBuilder(ResultInterface::class)
            ->getMockForAbstractClass();
        $resultSuccess->expects(static::once())
            ->method('isValid')
            ->willReturn(true);
        $resultFail = $this->getMockBuilder(ResultInterface::class)
            ->getMockForAbstractClass();
        $resultFail->expects(static::once())
            ->method('isValid')
            ->willReturn(false);
        $resultFail->expects(static::once())
            ->method('getFailsDescription')
            ->willReturn(['Fail']);
        $resultFail->expects(static::once())
            ->method('getErrorCodes')
            ->willReturn(['abc123']);

        $validator1->expects(static::once())
            ->method('validate')
            ->with($validationSubject)
            ->willReturn($resultSuccess);
        $validator2->expects(static::once())
            ->method('validate')
            ->with($validationSubject)
            ->willReturn($resultFail);

        $compositeResult = $this->getMockBuilder(ResultInterface::class)
            ->getMockForAbstractClass();
        $resultFactory = $this->getMockBuilder(ResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultFactory->expects(static::once())
            ->method('create')
            ->with(
                [
                    'isValid' => false,
                    'failsDescription' => ['Fail'],
                    'errorCodes' => ['abc123']
                ]
            )
            ->willReturn($compositeResult);

        $validatorComposite = new ValidatorComposite(
            $resultFactory,
            $tMapFactory,
            [
                'validator1' => ValidatorInterface::class,
                'validator2' => ValidatorInterface::class
            ]
        );
        static::assertSame($compositeResult, $validatorComposite->validate($validationSubject));
    }

    public function testValidateChainBreaksCorrectly()
    {
        $validationSubject = [];
        $validator1 = $this->getMockBuilder(ValidatorInterface::class)
            ->getMockForAbstractClass();
        $validator2 = $this->getMockBuilder(ValidatorInterface::class)
            ->getMockForAbstractClass();
        $tMapFactory = $this->getMockBuilder(TMapFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $tMap = $this->getMockBuilder(TMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $tMapFactory->expects($this->once())
            ->method('create')
            ->with(
                [
                    'array' => [
                        'validator1' => ValidatorInterface::class,
                        'validator2' => ValidatorInterface::class
                    ],
                    'type' => ValidatorInterface::class
                ]
            )
            ->willReturn($tMap);
        $tMap->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$validator1, $validator2]));

        $resultFail = $this->getMockBuilder(ResultInterface::class)
            ->getMockForAbstractClass();
        $resultFail->expects($this->once())
            ->method('isValid')
            ->willReturn(false);
        $resultFail->expects($this->once())
            ->method('getFailsDescription')
            ->willReturn(['Fail']);
        $resultFail->expects($this->once())
            ->method('getErrorCodes')
            ->willReturn(['abc123']);

        $validator1->expects($this->once())
            ->method('validate')
            ->with($validationSubject)
            ->willReturn($resultFail);

        // Assert this is never called
        $validator2->expects($this->never())
            ->method('validate');

        $compositeResult = $this->getMockBuilder(ResultInterface::class)
            ->getMockForAbstractClass();
        $resultFactory = $this->getMockBuilder(ResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultFactory->expects($this->once())
            ->method('create')
            ->with(
                [
                    'isValid' => false,
                    'failsDescription' => ['Fail'],
                    'errorCodes' => ['abc123']
                ]
            )
            ->willReturn($compositeResult);

        $validatorComposite = new ValidatorComposite(
            $resultFactory,
            $tMapFactory,
            [
                'validator1' => ValidatorInterface::class,
                'validator2' => ValidatorInterface::class
            ],
            ['validator1']
        );
        $this->assertSame($compositeResult, $validatorComposite->validate($validationSubject));
    }
}
