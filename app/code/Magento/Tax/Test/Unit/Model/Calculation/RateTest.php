<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\Calculation;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Tax\Model\Calculation\Rate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RateTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectHelper;

    /**
     * @var MockObject
     */
    protected $resourceMock;

    /**
     *  Init data
     */
    protected function setUp(): void
    {
        $this->objectHelper = new ObjectManager($this);
        $this->resourceMock = $this->getMockBuilder(AbstractResource::class)
            ->addMethods(['getIdFieldName'])
            ->onlyMethods(['getConnection', 'beginTransaction', 'rollBack'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resourceMock->expects($this->any())->method('beginTransaction')->willReturnSelf();
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
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage($exceptionMessage);
        $rate = $this->objectHelper->getObject(
            Rate::class,
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
                'exceptionMessage' => 'The required information is invalid. Verify the information and try again.',
                'data' => [
                    'zip_is_range' => true,
                    'zip_from' => '0111',
                    'zip_to' => '',
                    'code' => '',
                    'tax_country_id' => '',
                    'rate' => '',
                    'tax_postcode' => '',
                ],
            ],
            'fill all required fields 2' => [
                'exceptionMessage' => 'The required information is invalid. Verify the information and try again.',
                'data' => [
                    'zip_is_range' => '',
                    'zip_from' => '',
                    'zip_to' => '',
                    'code' => '',
                    'tax_country_id' => '',
                    'rate' => '0.2',
                    'tax_postcode' => '1234',
                ],
            ],
            'positive number' => [
                'exceptionMessage' => 'The Rate Percent is invalid. Enter a positive number and try again.',
                'data' => [
                    'zip_is_range' => '',
                    'zip_from' => '',
                    'zip_to' => '',
                    'code' => 'code',
                    'tax_country_id' => 'US',
                    'rate' => '-1',
                    'tax_postcode' => '1234',
                ],
            ],
            'zip code length' => [
                'exceptionMessage' => 'The ZIP Code length is invalid. '
                    . 'Verify that the length is nine characters or fewer and try again.',
                'data' => [
                    'zip_is_range' => true,
                    'zip_from' => '1234567890',
                    'zip_to' => '1234',
                    'code' => 'code',
                    'tax_country_id' => 'US',
                    'rate' => '1.1',
                    'tax_postcode' => '1234',
                ],
            ],
            'contain characters' => [
                'exceptionMessage' => 'The ZIP Code is invalid. Use numbers only.',
                'data' => [
                    'zip_is_range' => true,
                    'zip_from' => 'foo',
                    'zip_to' => '1234',
                    'code' => 'code',
                    'tax_country_id' => 'US',
                    'rate' => '1.1',
                    'tax_postcode' => '1234',
                ],
            ],
            'equal or greater' => [
                'exceptionMessage' => 'Range To should be equal or greater than Range From.',
                'data' => [
                    'zip_is_range' => true,
                    'zip_from' => '321',
                    'zip_to' => '123',
                    'code' => 'code',
                    'tax_country_id' => 'US',
                    'rate' => '1.1',
                    'tax_postcode' => '1234',
                ],
            ],
        ];
    }
}
