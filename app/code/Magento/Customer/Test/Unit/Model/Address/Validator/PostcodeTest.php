<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Address\Validator;

use Magento\Customer\Model\Address\Validator\Postcode;
use Magento\Directory\Helper\Data;
use PHPUnit\Framework\TestCase;

class PostcodeTest extends TestCase
{
    /**
     * Check postcode test
     *
     * @test
     */
    public function testIsValid()
    {
        $countryUs = 'US';
        $countryUa = 'UK';
        $helperMock = $this->createMock(Data::class);

        $helperMock->expects($this->any())
            ->method('isZipCodeOptional')
            ->willReturnMap(
                [
                    [$countryUs, true],
                    [$countryUa, false],
                ]
            );

        $validator = new Postcode($helperMock);
        $this->assertTrue($validator->isValid($countryUs, ''));
        $this->assertFalse($validator->isValid($countryUa, ''));
        $this->assertTrue($validator->isValid($countryUa, '123123'));
    }
}
