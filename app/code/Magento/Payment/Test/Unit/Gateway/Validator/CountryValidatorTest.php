<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Validator;

class CountryValidatorTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Payment\Gateway\Validator\CountryValidator */
    protected $model;

    /**
     * @var \Magento\Payment\Gateway\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Payment\Gateway\Validator\ResultInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var \Magento\Payment\Gateway\Validator\Result|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultMock;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(\Magento\Payment\Gateway\ConfigInterface::class)
            ->getMockForAbstractClass();
        $this->resultFactoryMock = $this->getMockBuilder(
            \Magento\Payment\Gateway\Validator\ResultInterfaceFactory::class
        )->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultMock = $this->getMockBuilder(\Magento\Payment\Gateway\Validator\Result::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new \Magento\Payment\Gateway\Validator\CountryValidator(
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
