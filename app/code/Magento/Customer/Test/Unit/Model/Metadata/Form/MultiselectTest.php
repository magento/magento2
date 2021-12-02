<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Metadata\Form;

use Magento\Customer\Api\Data\OptionInterface;
use Magento\Customer\Model\Metadata\ElementFactory;
use Magento\Customer\Model\Metadata\Form\Multiselect;
use Magento\Framework\App\RequestInterface;
use PHPUnit\Framework\MockObject\MockObject;

class MultiselectTest extends AbstractFormTestCase
{
    /**
     * Create an instance of the class that is being tested
     *
     * @param string|int|bool|null $value The value undergoing testing by a given test
     *
     * @return Multiselect
     */
    protected function getClass($value)
    {
        return new Multiselect(
            $this->localeMock,
            $this->loggerMock,
            $this->attributeMetadataMock,
            $this->localeResolverMock,
            $value,
            0
        );
    }

    /**
     * Test the Multiselect->extractValue() method
     *
     * @param string|int|bool|array $value to assign to boolean
     * @param bool $expected text output
     *
     * @return void
     * @dataProvider extractValueDataProvider
     */
    public function testExtractValue($value, $expected)
    {
        /** @var MockObject|Multiselect $multiselect */
        $multiselect = $this->getMockBuilder(
            Multiselect::class
        )->disableOriginalConstructor()
            ->setMethods(
                ['_getRequestValue']
            )->getMock();
        $multiselect->expects($this->once())->method('_getRequestValue')->willReturn($value);

        $request = $this->getMockBuilder(RequestInterface::class)
            ->getMock();
        $actual = $multiselect->extractValue($request);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Data provider for testExtractValue()
     *
     * @return array(array)
     */
    public function extractValueDataProvider()
    {
        return [
            'false' => [false, false],
            'int' => [15, [15]],
            'string' => ['some string', ['some string']],
            'array' => [[1, 2, 3], [1, 2, 3]]
        ];
    }

    /**
     * Test the Multiselect->compactValue() method
     *
     * @param string|int|bool|array $value to assign to boolean
     * @param bool $expected text output
     *
     * @return void
     * @dataProvider compactValueDataProvider
     */
    public function testCompactValue($value, $expected)
    {
        $multiselect = $this->getClass($value);
        $actual = $multiselect->compactValue($value);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Data provider for testCompactValue()
     *
     * @return array(array)
     */
    public function compactValueDataProvider()
    {
        return [
            'false' => [false, false],
            'int' => [15, 15],
            'string' => ['some string', 'some string'],
            'array' => [[1, 2, 3], '1,2,3']
        ];
    }

    /**
     * Test the Multiselect->outputValue() method with default TEXT format
     *
     * @param string|int|null|string[]|int[] $value
     * @param string $expected
     *
     * @return void
     * @dataProvider outputValueTextDataProvider
     */
    public function testOutputValueText($value, $expected)
    {
        $this->runOutputValueTest($value, $expected, ElementFactory::OUTPUT_FORMAT_TEXT);
    }

    /**
     * Test the Multiselect->outputValue() method with default HTML format
     *
     * @param string|int|null|string[]|int[] $value
     * @param string $expected
     *
     * @return void
     * @dataProvider outputValueTextDataProvider
     */
    public function testOutputValueHtml($value, $expected)
    {
        $this->runOutputValueTest($value, $expected, ElementFactory::OUTPUT_FORMAT_HTML);
    }

    /**
     * Data provider for testOutputValueText()
     *
     * @return array(array)
     */
    public function outputValueTextDataProvider()
    {
        return [
            'empty' => ['', ''],
            'null' => [false, ''],
            'number' => [14, 'fourteen'],
            'string' => ['some key', 'some string'],
            'array' => [[14, 'some key'], 'fourteen, some string'],
            'unknown' => [[14, 'some key', 'unknown'], 'fourteen, some string, ']
        ];
    }

    /**
     * Test the Multiselect->outputValue() method with JSON format
     *
     * @param string|int|null|string[]|int[] $value
     * @param string[] $expected
     *
     * @return void
     * @dataProvider outputValueJsonDataProvider
     */
    public function testOutputValueJson($value, $expected)
    {
        $this->runOutputValueTest($value, $expected, ElementFactory::OUTPUT_FORMAT_JSON);
    }

    /**
     * Data provider for testOutputValueJson()
     *
     * @return array(array)
     */
    public function outputValueJsonDataProvider()
    {
        return [
            'empty' => ['', ['']],
            'null' => [false, ['']],
            'number' => [14, ['14']],
            'string' => ['some key', ['some key']],
            'array' => [[14, 'some key'], ['14', 'some key']],
            'unknown' => [[14, 'some key', 'unknown'], ['14', 'some key', 'unknown']]
        ];
    }

    /**
     * Helper function that runs an outputValue test for a given format.
     *
     * @param string|int|null|string[]|int[] $value
     * @param string|string[] $expected
     * @param string $format
     */
    protected function runOutputValueTest($value, $expected, $format)
    {
        $option1 = $this->getMockBuilder(OptionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLabel', 'getValue'])
            ->getMockForAbstractClass();
        $option1->method('getLabel')
            ->willReturn('fourteen');
        $option1->method('getValue')
            ->willReturn('14');

        $option2 = $this->getMockBuilder(OptionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLabel', 'getValue'])
            ->getMockForAbstractClass();
        $option2->method('getLabel')
            ->willReturn('some string');
        $option2->method('getValue')
            ->willReturn('some key');

        $this->attributeMetadataMock->method(
            'getOptions'
        )->willReturn(
            [
                $option1,
                $option2,
            ]
        );
        $multiselect = $this->getClass($value);
        $actual = $multiselect->outputValue($format);
        $this->assertEquals($expected, $actual);
    }
}
