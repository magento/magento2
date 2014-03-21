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
namespace Magento\Exception;

/**
 * Class InputExceptionTest
 *
 * @package Magento\Exception
 */
class InputExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $inputException = InputException::create(
            InputException::INVALID_FIELD_RANGE,
            'quantity',
            -100,
            array('minValue' => 0)
        );

        $this->assertEquals(InputException::INPUT_EXCEPTION, $inputException->getCode());
        $this->assertStringStartsWith('One or more', $inputException->getMessage());
        $this->assertEquals(
            array(
                array(
                    'minValue' => 0,
                    'value' => -100,
                    'fieldName' => 'quantity',
                    'code' => InputException::INVALID_FIELD_RANGE
                )
            ),
            $inputException->getParams()
        );
    }

    public function testAddError()
    {
        $inputException = InputException::create(
            InputException::INVALID_FIELD_RANGE,
            'weight',
            -100,
            array('minValue' => 1)
        );

        $inputException->addError(InputException::REQUIRED_FIELD, 'name', '');

        $this->assertEquals(InputException::INPUT_EXCEPTION, $inputException->getCode());
        $this->assertStringStartsWith('One or more', $inputException->getMessage());
        $this->assertEquals(
            array(
                array(
                    'minValue' => 1,
                    'value' => -100,
                    'fieldName' => 'weight',
                    'code' => InputException::INVALID_FIELD_RANGE
                ),
                array('fieldName' => 'name', 'code' => InputException::REQUIRED_FIELD, 'value' => '')
            ),
            $inputException->getParams()
        );
    }
}
