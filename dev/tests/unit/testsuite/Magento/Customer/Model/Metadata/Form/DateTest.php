<?php
/**
 * test Magento\Customer\Model\Metadata\Form\Date
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

use Magento\Customer\Service\V1\Data\Eav\ValidationRule;
use Magento\Customer\Service\V1\Data\Eav\ValidationRuleBuilder;

class DateTest extends AbstractFormTestCase
{
    /** @var \Magento\Customer\Model\Metadata\Form\Date */
    protected $date;

    protected function setUp()
    {
        parent::setUp();
        $this->attributeMetadataMock->expects(
            $this->any()
        )->method(
            'getAttributeCode'
        )->will(
            $this->returnValue('date')
        );
        $this->attributeMetadataMock->expects(
            $this->any()
        )->method(
            'getStoreLabel'
        )->will(
            $this->returnValue('Space Date')
        );
        $this->attributeMetadataMock->expects(
            $this->any()
        )->method(
            'getInputFilter'
        )->will(
            $this->returnValue('date')
        );
        $this->date = new Date(
            $this->localeMock,
            $this->loggerMock,
            $this->attributeMetadataMock,
            $this->localeResolverMock,
            null,
            0
        );
    }

    public function testExtractValue()
    {
        $requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $requestMock->expects($this->once())->method('getParam')->will($this->returnValue('1999-1-2'));

        // yyyy-MM-dd
        $actual = $this->date->extractValue($requestMock);
        $this->assertEquals('1999-01-02', $actual);
    }

    /**
     * @param array|string $value Value to validate
     * @param array $validation Array of more validation metadata
     * @param bool $required Whether field is required
     * @param array|bool $expected Expected output
     *
     * @dataProvider validateValueDataProvider
     */
    public function testValidateValue($value, $validation, $required, $expected)
    {
        $validationRules = array();
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $ruleBuilder = $helper->getObject('\Magento\Customer\Service\V1\Data\Eav\ValidationRuleBuilder');
        $ruleBuilder->populateWithArray(array('name' => 'input_validation', 'value' => 'date'));
        $validationRules[] = new ValidationRule($ruleBuilder);
        if (is_array($validation)) {
            foreach ($validation as $ruleName => $ruleValue) {
                $ruleBuilder->populateWithArray(array('name' => $ruleName, 'value' => $ruleValue));
                $validationRules[] = new ValidationRule($ruleBuilder);
            }
        }

        $this->attributeMetadataMock->expects(
            $this->any()
        )->method(
            'getValidationRules'
        )->will(
            $this->returnValue($validationRules)
        );

        $this->attributeMetadataMock->expects($this->any())->method('isRequired')->will($this->returnValue($required));

        $actual = $this->date->validateValue($value);
        $this->assertEquals($expected, $actual);
    }

    public function validateValueDataProvider()
    {
        return array(
            'false value, load original' => array(false, array(), false, true),
            'Empty value, not required' => array('', array(), false, true),
            'Empty value, required' => array('', array(), true, array('"Space Date" is a required value.')),
            'Valid date, min set' => array('1961-5-5', array('date_range_min' => strtotime('4/12/1961')), false, true),
            'Below min, only min set' => array(
                '1957-10-4',
                array('date_range_min' => strtotime('1961/04/12')),
                false,
                array('Please enter a valid date equal to or greater than 12/04/1961 at Space Date.')
            ),
            'Below min, min and max set' => array(
                '1957-10-4',
                array('date_range_min' => strtotime('1961/04/12'), 'date_range_max' => strtotime('12/1/2013')),
                false,
                array('Please enter a valid date between 12/04/1961 and 01/12/2013 at Space Date.')
            ),
            'Above max, only max set' => array(
                '2014-1-30',
                array('date_range_max' => strtotime('12/1/2013')),
                false,
                array('Please enter a valid date less than or equal to 01/12/2013 at Space Date.')
            ),
            'Valid, min and max' => array(
                '1961-5-5',
                array('date_range_min' => strtotime('4/12/1961'), 'date_range_max' => strtotime('12/1/2013')),
                false,
                true
            ),
            'Invalid date' => array(
                'abc',
                array(),
                false,
                array('dateFalseFormat' => '"Space Date" does not fit the entered date format.')
            )
        );
    }

    /**
     * @param array|string $value value to pass to compactValue()
     * @param array|string|bool $expected expected output
     *
     * @dataProvider compactAndRestoreValueDataProvider
     */
    public function testCompactValue($value, $expected)
    {
        $this->assertSame($expected, $this->date->compactValue($value));
    }

    public function compactAndRestoreValueDataProvider()
    {
        return array(
            array(1, 1),
            array(false, false),
            array('', null),
            array('test', 'test'),
            array(array('element1', 'element2'), array('element1', 'element2'))
        );
    }

    /**
     * @param array|string $value Value to pass to restoreValue()
     * @param array|string|bool $expected Expected output
     *
     * @dataProvider compactAndRestoreValueDataProvider
     */
    public function testRestoreValue($value, $expected)
    {
        $this->assertSame($expected, $this->date->restoreValue($value));
    }

    public function testOutputValue()
    {
        $this->assertEquals(null, $this->date->outputValue());
        $date = new Date(
            $this->localeMock,
            $this->loggerMock,
            $this->attributeMetadataMock,
            $this->localeResolverMock,
            '2012/12/31',
            0
        );
        $this->assertEquals('2012-12-31', $date->outputValue());
    }
}
