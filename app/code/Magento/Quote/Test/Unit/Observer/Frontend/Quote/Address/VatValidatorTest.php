<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Observer\Frontend\Quote\Address;

use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Customer\Model\Vat;
use Magento\Framework\DataObject;
use Magento\Quote\Observer\Frontend\Quote\Address\VatValidator;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class VatValidatorTest extends TestCase
{
    /**
     * @var  VatValidator
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $customerAddressMock;

    /**
     * @var MockObject
     */
    protected $customerVatMock;

    /**
     * @var MockObject
     */
    protected $quoteAddressMock;

    /**
     * @var MockObject
     */
    protected $storeMock;

    /**
     * @var array
     */
    protected $testData;

    /**
     * @var DataObject
     */
    protected $validationResult;

    protected function setUp(): void
    {
        $this->customerAddressMock = $this->createMock(Address::class);
        $this->customerVatMock = $this->createMock(Vat::class);
        $this->customerVatMock->expects($this->any())
            ->method('getMerchantCountryCode')
            ->willReturn('merchantCountryCode');
        $this->customerVatMock->expects($this->any())
            ->method('getMerchantVatNumber')
            ->willReturn('merchantVatNumber');

        $this->storeMock = $this->createMock(Store::class);

        $this->quoteAddressMock = $this->createPartialMock(\Magento\Quote\Model\Quote\Address::class, [
                'getCountryId',
                'getVatId',
                'getValidatedCountryCode',
                'getValidatedVatNumber',
                'getVatIsValid',
                'getVatRequestId',
                'getVatRequestDate',
                'getVatRequestSuccess',
                'getAddressType',
                'save',
                '__wakeup'
            ]);

        $this->testData = [
            'is_valid' => true,
            'request_identifier' => 'test_request_identifier',
            'request_date' => 'test_request_date',
            'request_success' => true,
        ];

        $this->quoteAddressMock->expects(
            $this->any()
        )->method(
            'getVatIsValid'
        )->will(
            $this->returnValue($this->testData['is_valid'])
        );
        $this->quoteAddressMock->expects(
            $this->any()
        )->method(
            'getVatRequestId'
        )->will(
            $this->returnValue($this->testData['request_identifier'])
        );
        $this->quoteAddressMock->expects(
            $this->any()
        )->method(
            'getVatRequestDate'
        )->will(
            $this->returnValue($this->testData['request_date'])
        );
        $this->quoteAddressMock->expects(
            $this->any()
        )->method(
            'getVatRequestSuccess'
        )->will(
            $this->returnValue($this->testData['request_success'])
        );
        $this->quoteAddressMock->expects($this->any())->method('getCountryId')->will($this->returnValue('en'));
        $this->quoteAddressMock->expects($this->any())->method('getVatId')->will($this->returnValue('testVatID'));

        $this->validationResult = new DataObject($this->testData);

        $this->model = new VatValidator(
            $this->customerAddressMock,
            $this->customerVatMock
        );
    }

    public function testValidateWithDisabledValidationOnEachTransaction()
    {
        $this->customerVatMock->expects($this->never())->method('checkVatNumber');

        $this->customerAddressMock->expects(
            $this->once()
        )->method(
            'hasValidateOnEachTransaction'
        )->with(
            $this->storeMock
        )->will(
            $this->returnValue(false)
        );

        $this->quoteAddressMock->expects(
            $this->any()
        )->method(
            'getValidatedCountryCode'
        )->will(
            $this->returnValue('en')
        );

        $this->quoteAddressMock->expects(
            $this->any()
        )->method(
            'getValidatedVatNumber'
        )->will(
            $this->returnValue('testVatID')
        );

        $this->quoteAddressMock->expects($this->never())->method('save');

        $this->assertEquals(
            $this->validationResult,
            $this->model->validate($this->quoteAddressMock, $this->storeMock)
        );
    }

    public function testValidateWithEnabledValidationOnEachTransaction()
    {
        $this->customerVatMock->expects(
            $this->once()
        )->method(
            'checkVatNumber'
        )->with(
            'en',
            'testVatID',
            'merchantCountryCode',
            'merchantVatNumber'
        )->will(
            $this->returnValue($this->validationResult)
        );

        $this->customerAddressMock->expects(
            $this->once()
        )->method(
            'hasValidateOnEachTransaction'
        )->with(
            $this->storeMock
        )->will(
            $this->returnValue(true)
        );

        $this->quoteAddressMock->expects(
            $this->any()
        )->method(
            'getValidatedCountryCode'
        )->will(
            $this->returnValue('en')
        );

        $this->quoteAddressMock->expects(
            $this->any()
        )->method(
            'getValidatedVatNumber'
        )->will(
            $this->returnValue('testVatID')
        );

        $this->quoteAddressMock->expects($this->once())->method('save');

        $this->assertEquals(
            $this->validationResult,
            $this->model->validate($this->quoteAddressMock, $this->storeMock)
        );
    }

    public function testValidateWithDifferentCountryIdAndValidatedCountryCode()
    {
        $this->customerVatMock->expects(
            $this->once()
        )->method(
            'checkVatNumber'
        )->with(
            'en',
            'testVatID',
            'merchantCountryCode',
            'merchantVatNumber'
        )->will(
            $this->returnValue($this->validationResult)
        );

        $this->customerAddressMock->expects(
            $this->once()
        )->method(
            'hasValidateOnEachTransaction'
        )->with(
            $this->storeMock
        )->will(
            $this->returnValue(false)
        );

        $this->quoteAddressMock->expects(
            $this->any()
        )->method(
            'getValidatedCountryCode'
        )->will(
            $this->returnValue('someCountryCode')
        );

        $this->quoteAddressMock->expects($this->any())->method('getVatId')->will($this->returnValue('testVatID'));

        $this->quoteAddressMock->expects($this->once())->method('save');

        $this->assertEquals(
            $this->validationResult,
            $this->model->validate($this->quoteAddressMock, $this->storeMock)
        );
    }

    public function testValidateWithDifferentVatNumberAndValidatedVatNumber()
    {
        $this->customerVatMock->expects(
            $this->once()
        )->method(
            'checkVatNumber'
        )->with(
            'en',
            'testVatID',
            'merchantCountryCode',
            'merchantVatNumber'
        )->will(
            $this->returnValue($this->validationResult)
        );

        $this->customerAddressMock->expects(
            $this->once()
        )->method(
            'hasValidateOnEachTransaction'
        )->with(
            $this->storeMock
        )->will(
            $this->returnValue(false)
        );

        $this->quoteAddressMock->expects(
            $this->any()
        )->method(
            'getValidatedCountryCode'
        )->will(
            $this->returnValue('en')
        );

        $this->quoteAddressMock->expects($this->any())->method('getVatId')->will($this->returnValue('someVatID'));

        $this->quoteAddressMock->expects($this->once())->method('save');

        $this->assertEquals(
            $this->validationResult,
            $this->model->validate($this->quoteAddressMock, $this->storeMock)
        );
    }

    public function testIsEnabledWithBillingTaxCalculationAddressType()
    {
        $this->customerAddressMock->expects(
            $this->any()
        )->method(
            'isVatValidationEnabled'
        )->will(
            $this->returnValue(true)
        );

        $this->customerAddressMock->expects(
            $this->any()
        )->method(
            'getTaxCalculationAddressType'
        )->will(
            $this->returnValue(AbstractAddress::TYPE_BILLING)
        );

        $this->quoteAddressMock->expects(
            $this->any()
        )->method(
            'getAddressType'
        )->will(
            $this->returnValue(AbstractAddress::TYPE_SHIPPING)
        );

        $result = $this->model->isEnabled($this->quoteAddressMock, $this->storeMock);
        $this->assertFalse($result);
    }

    public function testIsEnabledWithEnabledVatValidation()
    {
        $this->customerAddressMock->expects(
            $this->any()
        )->method(
            'isVatValidationEnabled'
        )->will(
            $this->returnValue(true)
        );
        $result = $this->model->isEnabled($this->quoteAddressMock, $this->storeMock);
        $this->assertTrue($result);
    }
}
