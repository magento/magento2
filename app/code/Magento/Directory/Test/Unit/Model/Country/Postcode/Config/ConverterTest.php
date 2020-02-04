<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Test\Unit\Model\Country\Postcode\Config;

class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Model\Address\Config\Converter
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $booleanUtilsMock;

    protected function setUp()
    {
        $this->booleanUtilsMock = $this->createMock(\Magento\Framework\Stdlib\BooleanUtils::class);
        $this->model = new \Magento\Directory\Model\Country\Postcode\Config\Converter($this->booleanUtilsMock);
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
