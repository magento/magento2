<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Test\Unit\Validator;

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
            '\Magento\Framework\Exception\ValidatorException',
            'Extra parameters passed to parent construct: $factory. File: ' . $fileName
        );
        $this->_model->validate('Magento\SomeModule\Model\Four\Test');
    }

    public function testValidateIfClassHasMissingRequiredArguments()
    {
        $fileName = realpath(__DIR__ . '/../_files/app/code/Magento/SomeModule/Model/Five/Test.php');
        $fileName = str_replace('\\', '/', $fileName);
        $this->setExpectedException(
            '\Magento\Framework\Exception\ValidatorException',
            'Missed required argument factory in parent::__construct call. File: ' . $fileName
        );
        $this->_model->validate('Magento\SomeModule\Model\Five\Test');
    }

    public function testValidateIfClassHasIncompatibleArguments()
    {
        $fileName = realpath(__DIR__ . '/../_files/app/code/Magento/SomeModule/Model/Six/Test.php');
        $fileName = str_replace('\\', '/', $fileName);
        $this->setExpectedException(
            '\Magento\Framework\Exception\ValidatorException',
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
            '\Magento\Framework\Exception\ValidatorException',
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
            '\Magento\Framework\Exception\ValidatorException',
            'Incompatible argument type: Required type: array. ' . 'Actual type: \ClassB; File: ' . PHP_EOL . $fileName
        );
        $this->_model->validate('ClassArgumentWithWrongParentArgumentsType');
    }
}
