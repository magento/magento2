<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Model\Plugin\Store\Model\Validation;

use Magento\Store\Model\Validation\StoreCodeValidator as Subject;
use Magento\Webapi\Model\Plugin\Store\Model\Validation\StoreCodeValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreCodeValidatorTest extends TestCase
{
    /**
     * @var Subject|MockObject
     */
    private $subjectMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->subjectMock = $this->getMockBuilder(Subject::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return void
     */
    public function testAfterIsValidStoreCodeParsedCorrectly(): void
    {
        $invalidStoreCodeInUrl = "rest";
        $parsedStoreCode = "us";
        $storeCodeValidator = new StoreCodeValidator($invalidStoreCodeInUrl);
        $this->assertTrue($storeCodeValidator->afterIsValid($this->subjectMock, true, $parsedStoreCode));
    }

    /**
     * @return void
     */
    public function testAfterIsValidStoreCodeParsedIncorrectly(): void
    {
        $invalidStoreCodeInUrl = "rest";
        $parsedStoreCode = "rest";
        $storeCodeValidator = new StoreCodeValidator($invalidStoreCodeInUrl);
        $this->assertFalse($storeCodeValidator->afterIsValid($this->subjectMock, true, $parsedStoreCode));
        $invalidStoreCodeInUrl = "soap";
        $parsedStoreCode = "soap";
        $storeCodeValidator = new StoreCodeValidator($invalidStoreCodeInUrl);
        $this->assertFalse($storeCodeValidator->afterIsValid($this->subjectMock, true, $parsedStoreCode));
    }

    /**
     * @return void
     */
    public function testAfterValidIsFailedPreviousValidation(): void
    {
        $invalidStoreCodeInUrl = "rest";
        $parsedStoreCode = "us";
        $storeCodeValidator = new StoreCodeValidator($invalidStoreCodeInUrl);
        $this->assertFalse($storeCodeValidator->afterIsValid($this->subjectMock, false, $parsedStoreCode));
    }
}
