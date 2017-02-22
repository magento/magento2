<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Validator;

use Magento\Payment\Gateway\Validator\ValidatorComposite;
use Magento\Payment\Gateway\Validator\ValidatorInterface;

class ValidatorCompositeTest extends \PHPUnit_Framework_TestCase
{
    public function testValidate()
    {
        $validationSubject = [];
        $validator1 = $this->getMockBuilder('Magento\Payment\Gateway\Validator\ValidatorInterface')
            ->getMockForAbstractClass();
        $validator2 = $this->getMockBuilder('Magento\Payment\Gateway\Validator\ValidatorInterface')
            ->getMockForAbstractClass();
        $tMapFactory = $this->getMockBuilder('Magento\Framework\ObjectManager\TMapFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $tMap = $this->getMockBuilder('Magento\Framework\ObjectManager\TMap')
            ->disableOriginalConstructor()
            ->getMock();

        $tMapFactory->expects(static::once())
            ->method('create')
            ->with(
                [
                    'array' => [
                        'validator1' => 'Magento\Payment\Gateway\Validator\ValidatorInterface',
                        'validator2' => 'Magento\Payment\Gateway\Validator\ValidatorInterface'
                    ],
                    'type' => ValidatorInterface::class
                ]
            )
            ->willReturn($tMap);
        $tMap->expects(static::once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$validator1, $validator2]));

        $resultSuccess = $this->getMockBuilder('Magento\Payment\Gateway\Validator\ResultInterface')
            ->getMockForAbstractClass();
        $resultSuccess->expects(static::once())
            ->method('isValid')
            ->willReturn(true);
        $resultFail = $this->getMockBuilder('Magento\Payment\Gateway\Validator\ResultInterface')
            ->getMockForAbstractClass();
        $resultFail->expects(static::once())
            ->method('isValid')
            ->willReturn(false);
        $resultFail->expects(static::once())
            ->method('getFailsDescription')
            ->willReturn(['Fail']);

        $validator1->expects(static::once())
            ->method('validate')
            ->with($validationSubject)
            ->willReturn($resultSuccess);
        $validator2->expects(static::once())
            ->method('validate')
            ->with($validationSubject)
            ->willReturn($resultFail);

        $compositeResult = $this->getMockBuilder('Magento\Payment\Gateway\Validator\ResultInterface')
            ->getMockForAbstractClass();
        $resultFactory = $this->getMockBuilder('Magento\Payment\Gateway\Validator\ResultInterfaceFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultFactory->expects(static::once())
            ->method('create')
            ->with(
                [
                    'isValid' => false,
                    'failsDescription' => ['Fail']
                ]
            )
            ->willReturn($compositeResult);


        $validatorComposite = new ValidatorComposite(
            $resultFactory,
            $tMapFactory,
            [
                'validator1' => 'Magento\Payment\Gateway\Validator\ValidatorInterface',
                'validator2' => 'Magento\Payment\Gateway\Validator\ValidatorInterface'
            ]
        );
        static::assertSame($compositeResult, $validatorComposite->validate($validationSubject));
    }
}
