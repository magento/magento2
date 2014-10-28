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
        return new Multiline(
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
            array(
                'lines' => array(array('one', 'two'), true),
                'mixed lines' => array(array('one', '', ''), true),
                'empty lines' => array(array('', '', ''), true)
            )
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
            array(
                'long lines' => array(
                    array('0123456789', '0123456789'),
                    '"" length must be equal or less than 8 characters.'
                ),
                'long and short' => array(
                    array('0123456789', '01'),
                    '"" length must be equal or less than 8 characters.'
                ),
                'short and long' => array(
                    array('01', '0123456789'),
                    '"" length must be equal or greater than 4 characters.'
                )
            )
        );
    }
}
