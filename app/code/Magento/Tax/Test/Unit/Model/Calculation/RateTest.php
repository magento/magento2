<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model\Calculation;

class RateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     *  Init data
     */
    protected function setUp()
    {
        $this->objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->resourceMock = $this->getMock(
            'Magento\Framework\Model\ResourceModel\AbstractResource',
            ['_construct', 'getConnection', 'getIdFieldName', 'beginTransaction',
                'rollBack'],
            [],
            '',
            false
        );
        $this->resourceMock->expects($this->any())->method('beginTransaction')->will($this->returnSelf());
    }

    /**
     * Check if validation throws exceptions in case of incorrect input data
     *
     * @param string $exceptionMessage
     * @param array $data
     *
     * @dataProvider exceptionOfValidationDataProvider
     */
    public function testExceptionOfValidation($exceptionMessage, $data)
    {
        $this->setExpectedException('\Magento\Framework\Exception\LocalizedException', $exceptionMessage);
        $rate = $this->objectHelper->getObject(
            'Magento\Tax\Model\Calculation\Rate',
            ['resource' => $this->resourceMock]
        );
        foreach ($data as $key => $value) {
            $rate->setData($key, $value);
        }
        $rate->beforeSave();
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function exceptionOfValidationDataProvider()
    {
        return [
            'fill all required fields 1' => [
                'exceptionMessage' => 'Make sure all required information is valid.',
                'data' => ['zip_is_range' => true, 'zip_from' => '0111', 'zip_to' => '',
                    'code' => '', 'tax_country_id' => '', 'rate' => '', 'tax_postcode' => '', ],
            ],
            'fill all required fields 2' => [
                'exceptionMessage' => 'Make sure all required information is valid.',
                'data' => ['zip_is_range' => '', 'zip_from' => '', 'zip_to' => '',
                    'code' => '', 'tax_country_id' => '', 'rate' => '0.2', 'tax_postcode' => '1234', ], ],
            'positive number' => [
                'exceptionMessage' => 'The Rate Percent should be a positive number.',
                'data' => ['zip_is_range' => '', 'zip_from' => '', 'zip_to' => '', 'code' => 'code',
                    'tax_country_id' => 'US', 'rate' => '-1', 'tax_postcode' => '1234', ],
            ],
            'zip code length' => [
                'exceptionMessage' => 'Maximum zip code length is 9.',
                'data' => ['zip_is_range' => true, 'zip_from' => '1234567890', 'zip_to' => '1234',
                    'code' => 'code', 'tax_country_id' => 'US', 'rate' => '1.1', 'tax_postcode' => '1234', ],
            ],
            'contain characters' => [
                'exceptionMessage' => 'Use digits only for the zip code.',
                'data' => ['zip_is_range' => true, 'zip_from' => 'foo', 'zip_to' => '1234', 'code' => 'code',
                    'tax_country_id' => 'US', 'rate' => '1.1', 'tax_postcode' => '1234', ],
            ],
            'equal or greater' => [
                'exceptionMessage' => 'Range To should be equal or greater than Range From.',
                'data' => ['zip_is_range' => true, 'zip_from' => '321', 'zip_to' => '123', 'code' => 'code',
                    'tax_country_id' => 'US', 'rate' => '1.1', 'tax_postcode' => '1234', ],
            ],
        ];
    }
}
