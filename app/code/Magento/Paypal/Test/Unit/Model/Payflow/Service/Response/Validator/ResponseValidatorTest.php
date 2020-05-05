<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Payflow\Service\Response\Validator;

use Magento\Framework\DataObject;
use Magento\Paypal\Model\Payflow\Service\Response\Validator\ResponseValidator;
use Magento\Paypal\Model\Payflow\Service\Response\ValidatorInterface;
use Magento\Paypal\Model\Payflow\Transparent;
use Magento\Paypal\Model\Payflowpro;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ResponseValidatorTest
 *
 * Test for class \Magento\Paypal\Model\Payflow\Service\Response\Validator\ResponseValidator
 */
class ResponseValidatorTest extends TestCase
{
    /**
     * @var ResponseValidator
     */
    protected $responseValidator;

    /**
     * @var ValidatorInterface|MockObject
     */
    protected $validatorMock;

    /**
     * @var Transparent|MockObject
     */
    protected $payflowFacade;

    protected function setUp(): void
    {
        $this->validatorMock = $this->getMockBuilder(
            ValidatorInterface::class
        )
            ->setMethods(['validate'])
            ->getMockForAbstractClass();
        $this->payflowFacade = $this->getMockBuilder(Transparent::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->responseValidator = new ResponseValidator([$this->validatorMock]);
    }

    /**
     * @param Object $response
     * @param int $exactlyCount
     *
     * @dataProvider dataProviderForTestValidate
     */
    public function testValidate(DataObject $response, $exactlyCount)
    {
        $this->validatorMock->expects($this->exactly($exactlyCount))
            ->method('validate')
            ->with($response);

        $this->responseValidator->validate($response, $this->payflowFacade);
    }

    /**
     * @return array
     */
    public function dataProviderForTestValidate()
    {
        return [
            [
                'response' => new DataObject(['result' => Payflowpro::RESPONSE_CODE_APPROVED]),
                'exactlyCount' => 1
            ],
            [
                'response' => new DataObject(['result' => Payflowpro::RESPONSE_CODE_FRAUDSERVICE_FILTER]),
                'exactlyCount' => 1
            ],
            [
                'response' => new DataObject(['result' => Payflowpro::RESPONSE_CODE_INVALID_AMOUNT]),
                'exactlyCount' => 0
            ]
        ];
    }

    public function testValidateFail()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Transaction has been declined');
        $response = new DataObject(
            [
                'result' => Payflowpro::RESPONSE_CODE_APPROVED,
                'respmsg' => 'Test error msg',
            ]
        );

        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($response)
            ->willReturn(false);

        $this->responseValidator->validate($response, $this->payflowFacade);
    }

    public function testValidateUnknownCode()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Transaction has been declined');
        $response = new DataObject(
            [
                'result' => 7777777777,
                'respmsg' => 'Test error msg',
            ]
        );

        $this->validatorMock->expects($this->never())
            ->method('validate')
            ->with($response)
            ->willReturn(false);

        $this->responseValidator->validate($response, $this->payflowFacade);
    }
}
