<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model\Country\Postcode\Config;

use Magento\Customer\Model\Address\Config\Converter as AddressConverter;
use Magento\Directory\Model\Country\Postcode\Config\Converter as CountryConverter;
use Magento\Framework\Stdlib\BooleanUtils;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    /**
     * @var AddressConverter
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $booleanUtilsMock;

    protected function setUp(): void
    {
        $this->booleanUtilsMock = $this->createMock(BooleanUtils::class);
        $this->model = new CountryConverter($this->booleanUtilsMock);
    }

    public function testConvert()
    {
        $inputData = new \DOMDocument();
        $this->booleanUtilsMock->expects($this->any())->method('toBoolean')->willReturn(true);
        $inputData->load(__DIR__ . '/../../../../_files/zip_codes.xml');
        $expectedResult = require __DIR__ . '/../../../../_files/zip_codes.php';
        $this->assertEquals($expectedResult, $this->model->convert($inputData));
    }
}
