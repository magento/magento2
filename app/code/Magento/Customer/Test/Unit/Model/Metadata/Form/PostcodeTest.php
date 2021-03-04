<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model\Metadata\Form;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Customer\Model\Metadata\Form\Postcode;

class PostcodeTest extends AbstractFormTestCase
{
    /**
     * @var DirectoryHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $directoryHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directoryHelper = $this->getMockBuilder(\Magento\Directory\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Create an instance of the class that is being tested
     *
     * @param string|int|bool|null $value The value undergoing testing by a given test
     * @return Postcode
     */
    protected function getClass($value)
    {
        return new \Magento\Customer\Model\Metadata\Form\Postcode(
            $this->localeMock,
            $this->loggerMock,
            $this->attributeMetadataMock,
            $this->localeResolverMock,
            $value,
            0,
            false,
            $this->directoryHelper
        );
    }

    /**
     * @param string $value to assign to boolean
     * @param bool $expected text output
     * @param string $countryId
     * @param bool $isOptional
     *
     * @dataProvider validateValueDataProvider
     */
    public function testValidateValue($value, $expected, $countryId, $isOptional)
    {
        $storeLabel = 'Zip/Postal Code';
        $this->attributeMetadataMock->expects($this->once())
            ->method('getStoreLabel')
            ->willReturn($storeLabel);

        $this->directoryHelper->expects($this->once())
            ->method('isZipCodeOptional')
            ->willReturnMap([
                [$countryId, $isOptional],
            ]);

        $object = $this->getClass($value);
        $object->setExtractedData(['country_id' => $countryId]);

        $actual = $object->validateValue($value);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function validateValueDataProvider()
    {
        return [
            ['', ['"Zip/Postal Code" is a required value.'], 'US', false],
            ['90034', true, 'US', false],
            ['', true, 'IE', true],
            ['90034', true, 'IE', true],
        ];
    }
}
