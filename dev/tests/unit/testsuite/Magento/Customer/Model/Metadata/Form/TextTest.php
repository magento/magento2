<?php
/**
 * test Magento\Customer\Model\Metadata\Form\Text
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Model\Metadata\Form;

class TextTest extends AbstractFormTestCase
{
    /** @var \Magento\Stdlib\String */
    protected $stringHelper;

    protected function setUp()
    {
        parent::setUp();
        $this->stringHelper = new \Magento\Stdlib\String();
    }

    /**
     * Create an instance of the class that is being tested
     *
     * @param string|int|bool|null $value The value undergoing testing by a given test
     * @return Text
     */
    protected function getClass($value)
    {
        return new Text(
            $this->localeMock,
            $this->loggerMock,
            $this->attributeMetadataMock,
            $value,
            0,
            false,
            $this->stringHelper
        );
    }

    /**
     * @param string|int|bool $value to assign to boolean
     * @param bool $expected text output
     * @dataProvider validateValueDataProvider
     */
    public function testValidateValue($value, $expected)
    {
        $sut = $this->getClass($value);
        $actual = $sut->validateValue($value);
        $this->assertEquals($expected, $actual);
    }

    public function validateValueDataProvider()
    {
        return [
            'empty' => ['', true],
            '0' => [0, true],
            'zero' => ['0', true],
            'string' => ['some text', true],
            'number' => [123, true],
            'true' => [true, true],
            'false' => [false, true]
        ];
    }

    /**
     * @param string|int|bool|null $value to assign to boolean
     * @param string|bool|null $expected text output
     * @dataProvider validateValueRequiredDataProvider
     */
    public function testValidateValueRequired($value, $expected)
    {
        $this->attributeMetadataMock
            ->expects($this->any())
            ->method('isRequired')
            ->will($this->returnValue(true));

        $sut = $this->getClass($value);
        $actual = $sut->validateValue($value);

        if (is_bool($actual)) {
            $this->assertEquals($expected, $actual);
        } else {
            $this->assertContains($expected, $actual);
        }
    }

    public function validateValueRequiredDataProvider()
    {
        return [
            'empty' => ['', '"" is a required value.'],
            'null' => [null, '"" is a required value.'],
            '0'  => [0, true],
            'zero'  => ['0', true],
            'string' => ['some text', true],
            'number' => [123, true],
            'true' => [true, true],
            'false' => [false, '"" is a required value.'],
        ];
    }

    /**
     * @param string|int|bool|null $value to assign to boolean
     * @param string|bool $expected text output
     * @dataProvider validateValueLengthDataProvider
     */
    public function testValidateValueLength($value, $expected)
    {
        $this->attributeMetadataMock
            ->expects($this->any())
            ->method('getValidationRules')
            ->will($this->returnValue(['min_text_length' => 4, 'max_text_length' => 8]));

        $sut = $this->getClass($value);
        $actual = $sut->validateValue($value);

        if (is_bool($actual)) {
            $this->assertEquals($expected, $actual);
        } else {
            $this->assertContains($expected, $actual);
        }
    }

    public function validateValueLengthDataProvider()
    {
        return [
            'false' => [false, true],
            'empty' => ['', true],
            'null' => [null, true],
            'true' => [true, '"" length must be equal or greater than 4 characters.'],
            'one' => [1, '"" length must be equal or greater than 4 characters.'],
            'L1' => ['a', '"" length must be equal or greater than 4 characters.'],
            'L3' => ['abc', '"" length must be equal or greater than 4 characters.'],
            'L4' => ['abcd', true],
            'thousand' => [1000, true],
            'L8' => ['abcdefgh', true],
            'L9' => ['abcdefghi', '"" length must be equal or less than 8 characters.'],
            'L12' => ['abcdefghjkl', '"" length must be equal or less than 8 characters.'],
            'billion' => [1000000000, '"" length must be equal or less than 8 characters.'],
        ];
    }
}
