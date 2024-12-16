<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\ValidationRules;

use Magento\Quote\Model\Quote\Address;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ValidationRules\BillingAddressValidationRule;
use PHPUnit\Framework\TestCase;

class BillingAddressValidationRuleTest extends TestCase
{
    /**
     * @var BillingAddressValidationRule
     */
    private $model;

    /**
     * @var ValidationResultFactory|MockObject
     */
    private $validationResultFactoryMock;

    protected function setUp(): void
    {
        $this->validationResultFactoryMock = $this->getMockBuilder(ValidationResultFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->model = new BillingAddressValidationRule($this->validationResultFactoryMock);
    }

    public function testValidate()
    {
        $storeId = 1;
        $error = new \Magento\Framework\Phrase(
            'A customer with the same email address already exists in an associated website.'
        );
        $validationResult = [$error];
        $validationResultObj = new \Magento\Framework\Validation\ValidationResult($validationResult);
        $this->validationResultFactoryMock->expects($this->once())->method('create')->with(
            ['errors' => $validationResult]
        )->willReturn($validationResultObj);
        $addressMock = $this->getMockBuilder(Address::class)->disableOriginalConstructor()->getMock();
        $addressMock->expects($this->once())->method('validate')->willReturn($validationResult);
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->once())->method('getBillingAddress')->willReturn($addressMock);
        $quoteMock->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $quoteMock->expects($this->any())->method('__call')->with('getCustomerId')
            ->willReturn(null);
        $quoteMock->expects($this->once())->method('getOrigData')->willReturn(['customer_id' => 2]);
        $result = $this->model->validate($quoteMock);
        $this->assertIsArray($result);
        $this->assertEquals(
            'A customer with the same email address already exists in an associated website.',
            $result[0]->getErrors()[0]->getText()
        );
    }
}
