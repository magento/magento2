<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Metadata\Form;

use Magento\Customer\Model\Metadata\Form\Select;

/**
 * test Magento\Customer\Model\Metadata\Form\Select
 */
class SelectTest extends AbstractFormTestCase
{
    /**
     * Create an instance of the class that is being tested
     *
     * @param string|int|bool|null $value The value undergoing testing by a given test
     * @return Select
     */
    protected function getClass($value)
    {
        return new \Magento\Customer\Model\Metadata\Form\Select(
            $this->localeMock,
            $this->loggerMock,
            $this->attributeMetadataMock,
            $this->localeResolverMock,
            $value,
            0
        );
    }

    /**
     * @param string|int|bool|null $value to assign to Select
     * @param bool $expected text output
     * @dataProvider validateValueDataProvider
     */
    public function testValidateValue($value, $expected)
    {
        $select = $this->getClass($value);
        $actual = $select->validateValue($value);
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
     * @param string|bool $expected text output
     * @dataProvider validateValueRequiredDataProvider
     */
    public function testValidateValueRequired($value, $expected)
    {
        $this->attributeMetadataMock->expects($this->any())->method('isRequired')->will($this->returnValue(true));

        $select = $this->getClass($value);
        $actual = $select->validateValue($value);

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
            '0' => [0, true],
            'string' => ['some text', true],
            'number' => [123, true],
            'true' => [true, true],
            'false' => [false, '"" is a required value.']
        ];
    }

    /**
     * @param string|int|bool|null $value
     * @param string|int $expected
     * @dataProvider outputValueDataProvider
     */
    public function testOutputValue($value, $expected)
    {
        $option1 = $this->getMockBuilder('Magento\Customer\Api\Data\OptionInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getLabel', 'getValue'])
            ->getMockForAbstractClass();
        $option1->expects($this->any())
            ->method('getLabel')
            ->will($this->returnValue('fourteen'));
        $option1->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue('14'));

        $option2 = $this->getMockBuilder('Magento\Customer\Api\Data\OptionInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getLabel', 'getValue'])
            ->getMockForAbstractClass();
        $option2->expects($this->any())
            ->method('getLabel')
            ->will($this->returnValue('some string'));
        $option2->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue('some key'));

        $option3 = $this->getMockBuilder('Magento\Customer\Api\Data\OptionInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getLabel', 'getValue'])
            ->getMockForAbstractClass();
        $option3->expects($this->any())
            ->method('getLabel')
            ->will($this->returnValue('True'));
        $option3->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue('true'));

        $this->attributeMetadataMock->expects(
            $this->any()
        )->method(
            'getOptions'
        )->will(
            $this->returnValue(
                [
                    $option1,
                    $option2,
                    $option3,
                ]
            )
        );
        $select = $this->getClass($value);
        $actual = $select->outputValue();
        $this->assertEquals($expected, $actual);
    }

    public function outputValueDataProvider()
    {
        return [
            'empty' => ['', ''],
            'null' => [null, ''],
            'number' => [14, 'fourteen'],
            'string' => ['some key', 'some string'],
            'boolean' => [true, ''],
            'unknown' => ['unknownKey', ''],
            'true' => ['true', 'True']
        ];
    }
}
