<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Metadata\Form;

use Magento\Customer\Model\Metadata\Form\Multiline;

/** Test Magento\Customer\Model\Metadata\Form\Multiline */
class MultilineTest extends TextTest
{
    /**
     * Create an instance of the class that is being tested
     *
     * @param string|int|bool|null $value The value undergoing testing by a given test
     * @return Multiline
     */
    protected function getClass($value)
    {
        return new \Magento\Customer\Model\Metadata\Form\Multiline(
            $this->localeMock,
            $this->loggerMock,
            $this->attributeMetadataMock,
            $this->localeResolverMock,
            $value,
            0,
            false,
            $this->stringHelper
        );
    }

    /**
     * @param string|int|bool|null $value to assign to boolean
     * @param string|bool|null $expected text output
     * @dataProvider validateValueRequiredDataProvider
     */
    public function testValidateValueRequired($value, $expected)
    {
        $this->attributeMetadataMock->expects($this->any())->method('getMultilineCount')->will($this->returnValue(5));

        parent::testValidateValueRequired($value, $expected);
    }

    public function validateValueRequiredDataProvider()
    {
        return array_merge(
            parent::validateValueRequiredDataProvider(),
            [
                'lines' => [['one', 'two'], true],
                'mixed lines' => [['one', '', ''], true],
                'empty lines' => [['', '', ''], true]
            ]
        );
    }

    /**
     * @param string|int|bool|null $value to assign to boolean
     * @param string|bool $expected text output
     * @dataProvider validateValueLengthDataProvider
     */
    public function testValidateValueLength($value, $expected)
    {
        $this->attributeMetadataMock->expects($this->any())->method('getMultilineCount')->will($this->returnValue(5));

        parent::testValidateValueLength($value, $expected);
    }

    public function validateValueLengthDataProvider()
    {
        return array_merge(
            parent::validateValueLengthDataProvider(),
            [
                'long lines' => [
                    ['0123456789', '0123456789'],
                    '"" length must be equal or less than 8 characters.',
                ],
                'long and short' => [
                    ['0123456789', '01'],
                    '"" length must be equal or less than 8 characters.',
                ],
                'short and long' => [
                    ['01', '0123456789'],
                    '"" length must be equal or greater than 4 characters.',
                ]
            ]
        );
    }
}
