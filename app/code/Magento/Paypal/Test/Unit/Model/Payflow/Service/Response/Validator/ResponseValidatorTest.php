<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Payflow\Service\Response\Validator;

use Magento\Framework\DataObject;
use Magento\Paypal\Model\Payflow\Transparent;
use Magento\Paypal\Model\Payflowpro;
use Magento\Paypal\Model\Payflow\Service\Response\ValidatorInterface;
use Magento\Paypal\Model\Payflow\Service\Response\Validator\ResponseValidator;

/**
 * Class ResponseValidatorTest
 *
 * Test for class \Magento\Paypal\Model\Payflow\Service\Response\Validator\ResponseValidator
 */
class ResponseValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResponseValidator
     */
    protected $responseValidator;

    /**
     * @var ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorMock;

    /**
     * @var Transparent|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payflowFacade;

    protected function setUp()
    {
        $this->validatorMock = $this->getMockBuilder(
            \Magento\Paypal\Model\Payflow\Service\Response\ValidatorInterface::class
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

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Transaction has been declined
     */
    public function testValidateFail()
    {
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

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Transaction has been declined
     */
    public function testValidateUnknownCode()
    {
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
