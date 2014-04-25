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
namespace Magento\Framework\Code\Validator;


require_once __DIR__ . '/../_files/app/code/Magento/SomeModule/Model/Three/Test.php';
require_once __DIR__ . '/../_files/app/code/Magento/SomeModule/Model/Two/Test.php';
require_once __DIR__ . '/../_files/app/code/Magento/SomeModule/Model/One/Test.php';
require_once __DIR__ . '/../_files/app/code/Magento/SomeModule/Model/Four/Test.php';
require_once __DIR__ . '/../_files/app/code/Magento/SomeModule/Model/Five/Test.php';
require_once __DIR__ . '/../_files/app/code/Magento/SomeModule/Model/Six/Test.php';
require_once __DIR__ . '/_files/ClassesForConstructorIntegrity.php';
class ConstructorIntegrityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Code\Validator\ConstructorIntegrity
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Framework\Code\Validator\ConstructorIntegrity();
    }

    public function testValidateIfParentClassExist()
    {
        $this->assertEquals(true, $this->_model->validate('Magento\SomeModule\Model\One\Test'));
    }

    public function testValidateIfClassHasParentConstructCall()
    {
        $this->assertEquals(true, $this->_model->validate('Magento\SomeModule\Model\Two\Test'));
    }

    public function testValidateIfClassHasArgumentsQtyEqualToParentClass()
    {
        $this->assertEquals(true, $this->_model->validate('Magento\SomeModule\Model\Three\Test'));
    }

    public function testValidateIfClassHasExtraArgumentInTheParentConstructor()
    {
        $fileName = realpath(__DIR__ . '/../_files/app/code/Magento/SomeModule/Model/Four/Test.php');
        $fileName = str_replace('\\', '/', $fileName);
        $this->setExpectedException(
            '\Magento\Framework\Code\ValidationException',
            'Extra parameters passed to parent construct: $factory. File: ' . $fileName
        );
        $this->_model->validate('Magento\SomeModule\Model\Four\Test');
    }

    public function testValidateIfClassHasMissingRequiredArguments()
    {
        $fileName = realpath(__DIR__ . '/../_files/app/code/Magento/SomeModule/Model/Five/Test.php');
        $fileName = str_replace('\\', '/', $fileName);
        $this->setExpectedException(
            '\Magento\Framework\Code\ValidationException',
            'Missed required argument factory in parent::__construct call. File: ' . $fileName
        );
        $this->_model->validate('Magento\SomeModule\Model\Five\Test');
    }

    public function testValidateIfClassHasIncompatibleArguments()
    {
        $fileName = realpath(__DIR__ . '/../_files/app/code/Magento/SomeModule/Model/Six/Test.php');
        $fileName = str_replace('\\', '/', $fileName);
        $this->setExpectedException(
            '\Magento\Framework\Code\ValidationException',
            'Incompatible argument type: Required type: \Magento\SomeModule\Model\Proxy. ' .
            'Actual type: \Magento\SomeModule\Model\ElementFactory; File: ' .
            PHP_EOL .
            $fileName
        );
        $this->_model->validate('Magento\SomeModule\Model\Six\Test');
    }

    public function testValidateWrongOrderForParentArguments()
    {
        $fileName = realpath(__DIR__) . '/_files/ClassesForConstructorIntegrity.php';
        $fileName = str_replace('\\', '/', $fileName);
        $this->setExpectedException(
            '\Magento\Framework\Code\ValidationException',
            'Incompatible argument type: Required type: \Context. ' .
            'Actual type: \ClassA; File: ' .
            PHP_EOL .
            $fileName
        );
        $this->_model->validate('ClassArgumentWrongOrderForParentArguments');
    }

    public function testValidateWrongOptionalParamsType()
    {
        $fileName = realpath(__DIR__) . '/_files/ClassesForConstructorIntegrity.php';
        $fileName = str_replace('\\', '/', $fileName);
        $this->setExpectedException(
            '\Magento\Framework\Code\ValidationException',
            'Incompatible argument type: Required type: array. ' . 'Actual type: \ClassB; File: ' . PHP_EOL . $fileName
        );
        $this->_model->validate('ClassArgumentWithWrongParentArgumentsType');
    }
}
