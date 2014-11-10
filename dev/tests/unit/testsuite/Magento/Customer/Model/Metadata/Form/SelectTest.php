<?php
/**
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
        return new Select(
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
        return array(
            'empty' => array('', true),
            '0' => array(0, true),
            'zero' => array('0', true),
            'string' => array('some text', true),
            'number' => array(123, true),
            'true' => array(true, true),
            'false' => array(false, true)
        );
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
        return array(
            'empty' => array('', '"" is a required value.'),
            'null' => array(null, '"" is a required value.'),
            '0' => array(0, true),
            'string' => array('some text', true),
            'number' => array(123, true),
            'true' => array(true, true),
            'false' => array(false, '"" is a required value.')
        );
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
                array(
                    $option1,
                    $option2,
                    $option3
                )
            )
        );
        $select = $this->getClass($value);
        $actual = $select->outputValue();
        $this->assertEquals($expected, $actual);
    }

    public function outputValueDataProvider()
    {
        return array(
            'empty' => array('', ''),
            'null' => array(null, ''),
            'number' => array(14, 'fourteen'),
            'string' => array('some key', 'some string'),
            'boolean' => array(true, ''),
            'unknown' => array('unknownKey', ''),
            'true' => array('true', 'True')
        );
    }
}
