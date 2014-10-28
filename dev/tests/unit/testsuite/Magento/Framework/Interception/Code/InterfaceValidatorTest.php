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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Interception\Code;

class InterfaceValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject
     */
    protected $argumentsReaderMock;

    /**
     * @var \Magento\Framework\Interception\Code\InterfaceValidator
     */
    protected $model;

    protected function setUp()
    {
        $this->argumentsReaderMock = $this->getMock(
            '\Magento\Framework\Code\Reader\ArgumentsReader', array(), array(), '', false
        );

        $this->argumentsReaderMock->expects($this->any())->method('isCompatibleType')
            ->will($this->returnCallback(function ($arg1, $arg2) {
                return ltrim($arg1, '\\') == ltrim($arg2, '\\');
            }));

        $this->model = new InterfaceValidator($this->argumentsReaderMock);
    }

    /**
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::getMethodParameters
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::getMethodType
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::getOriginMethodName
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::getParametersType
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::__construct
     */
    public function testValidate()
    {
        $this->model->validate(
            '\Magento\Framework\Interception\Custom\Module\Model\InterfaceValidator\ItemPlugin\ValidPlugin',
            '\Magento\Framework\Interception\Custom\Module\Model\InterfaceValidator\ItemWithArguments'
        );
    }

    /**
     * @expectedException \Magento\Framework\Interception\Code\ValidatorException
     * @expectedExceptionMessage Incorrect interface in
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     */
    public function testValidateIncorrectInterface()
    {
        $this->model->validate(
            '\Magento\Framework\Interception\Custom\Module\Model\InterfaceValidator\ItemPlugin\IncompatibleInterface',
            '\Magento\Framework\Interception\Custom\Module\Model\InterfaceValidator\Item'
        );
    }

    /**
     * @expectedException \Magento\Framework\Interception\Code\ValidatorException
     * @expectedExceptionMessage Invalid [\Magento\Framework\Interception\Custom\Module\Model\Item] $subject type
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     */
    public function testValidateIncorrectSubjectType()
    {
        $this->model->validate(
            '\Magento\Framework\Interception\Custom\Module\Model\InterfaceValidator\ItemPlugin\IncorrectSubject',
            '\Magento\Framework\Interception\Custom\Module\Model\InterfaceValidator\Item'
        );
    }

    /**
     * @expectedException \Magento\Framework\Interception\Code\ValidatorException
     * @expectedExceptionMessage Invalid method signature. Invalid method parameters count
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validateMethodsParameters
     */
    public function testValidateIncompatibleMethodArgumentsCount()
    {
        $this->model->validate(
            '\Magento\Framework\Interception\Custom\Module\Model'
                . '\InterfaceValidator\ItemPlugin\IncompatibleArgumentsCount',
            '\Magento\Framework\Interception\Custom\Module\Model\InterfaceValidator\Item'
        );
    }

    /**
     * @expectedException \Magento\Framework\Interception\Code\ValidatorException
     * @expectedExceptionMessage Incompatible parameter type
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validateMethodsParameters
     */
    public function testValidateIncompatibleMethodArgumentsType()
    {
        $this->model->validate(
            '\Magento\Framework\Interception\Custom\Module\Model'
                . '\InterfaceValidator\ItemPlugin\IncompatibleArgumentsType',
            '\Magento\Framework\Interception\Custom\Module\Model\InterfaceValidator\ItemWithArguments'
        );
    }

    /**
     * @expectedException \Magento\Framework\Interception\Code\ValidatorException
     * @expectedExceptionMessage Invalid method signature. Detected extra parameters
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     */
    public function testValidateExtraParameters()
    {
        $this->model->validate(
            '\Magento\Framework\Interception\Custom\Module\Model\InterfaceValidator\ItemPlugin\ExtraParameters',
            '\Magento\Framework\Interception\Custom\Module\Model\InterfaceValidator\Item'
        );
    }

    /**
     * @expectedException \Magento\Framework\Interception\Code\ValidatorException
     * @expectedExceptionMessage Invalid [] $name type in
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     */
    public function testValidateInvalidProceed()
    {
        $this->model->validate(
            '\Magento\Framework\Interception\Custom\Module\Model\InterfaceValidator\ItemPlugin\InvalidProceed',
            '\Magento\Framework\Interception\Custom\Module\Model\InterfaceValidator\Item'
        );
    }
}
