<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Gateway\Validator;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Validator\CountryValidator;
use Magento\Payment\Gateway\Validator\Result;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CountryValidatorTest extends TestCase
{
    /** @var CountryValidator */
    protected $model;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $configMock;

    /**
     * @var ResultInterfaceFactory|MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var Result|MockObject
     */
    protected $resultMock;

    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();
        $this->resultFactoryMock = $this->getMockBuilder(
            ResultInterfaceFactory::class
        )->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultMock = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new CountryValidator(
            $this->resultFactoryMock,
            $this->configMock
        );
    }

    /**
     * @dataProvider validateAllowspecificTrueDataProvider
     */
    public function testValidateAllowspecificTrue($storeId, $country, $allowspecific, $specificcountry, $isValid)
    {
        $validationSubject = ['storeId' => $storeId, 'country' => $country];

        $this->configMock->expects($this->at(0))
            ->method('getValue')
            ->with('allowspecific', $storeId)
            ->willReturn($allowspecific);
        $this->configMock->expects($this->at(1))
            ->method('getValue')
            ->with('specificcountry', $storeId)
            ->willReturn($specificcountry);

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(['isValid' => $isValid, 'failsDescription' => [], 'errorCodes' => []])
            ->willReturn($this->resultMock);

        $this->assertSame($this->resultMock, $this->model->validate($validationSubject));
    }

    /**
     * @return array
     */
    public function validateAllowspecificTrueDataProvider()
    {
        return [
            [1, 'US', 1, 'US,UK,CA', true], //$storeId, $country, $allowspecific, $specificcountry, $isValid
            [1, 'BJ', 1, 'US,UK,CA', false]
        ];
    }

    /**
     * @dataProvider validateAllowspecificFalseDataProvider
     */
    public function testValidateAllowspecificFalse($storeId, $allowspecific, $isValid)
    {
        $validationSubject = ['storeId' => $storeId];

        $this->configMock->expects($this->at(0))
            ->method('getValue')
            ->with('allowspecific', $storeId)
            ->willReturn($allowspecific);

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(['isValid' => $isValid, 'failsDescription' => [], 'errorCodes' => []])
            ->willReturn($this->resultMock);

        $this->assertSame($this->resultMock, $this->model->validate($validationSubject));
    }

    /**
     * @return array
     */
    public function validateAllowspecificFalseDataProvider()
    {
        return [
            [1, 0, true] //$storeId, $allowspecific, $isValid
        ];
    }
}
