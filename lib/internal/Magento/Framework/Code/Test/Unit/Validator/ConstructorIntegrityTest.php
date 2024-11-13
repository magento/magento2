<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Test\Unit\Validator;

use Magento\SomeModule\Model\NamedArguments\TestNamedParameters;
use Magento\SomeModule\Model\NamedArguments\TestMixedParameters;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Code\Validator\ConstructorIntegrity;
use Magento\Framework\Exception\ValidatorException;

require_once __DIR__ . '/../_files/app/code/Magento/SomeModule/Model/Three/TestThree.php';
require_once __DIR__ . '/../_files/app/code/Magento/SomeModule/Model/Two/TestTwo.php';
require_once __DIR__ . '/../_files/app/code/Magento/SomeModule/Model/One/TestOne.php';
require_once __DIR__ . '/../_files/app/code/Magento/SomeModule/Model/Four/TestFour.php';
require_once __DIR__ . '/../_files/app/code/Magento/SomeModule/Model/Five/TestFive.php';
require_once __DIR__ . '/../_files/app/code/Magento/SomeModule/Model/Six/TestSix.php';
require_once __DIR__ . '/../_files/app/code/Magento/SomeModule/Model/NamedArguments/TestNamedParameters.php';
require_once __DIR__ . '/../_files/app/code/Magento/SomeModule/Model/NamedArguments/TestMixedParameters.php';
require_once __DIR__ . '/_files/ClassesForConstructorIntegrity.php';
class ConstructorIntegrityTest extends TestCase
{
    /**
     * @var ConstructorIntegrity
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new ConstructorIntegrity();
    }

    public function testValidateIfParentClassExist()
    {
        $this->assertTrue($this->_model->validate(\Magento\SomeModule\Model\One\TestOne::class));
    }

    public function testValidateIfClassHasParentConstructCall()
    {
        $this->assertTrue($this->_model->validate(\Magento\SomeModule\Model\Two\TestTwo::class));
    }

    public function testValidateIfClassHasParentConstructCallWithNamedArguments()
    {
        $this->assertTrue($this->_model->validate(TestNamedParameters::class));
    }

    public function testValidateIfClassHasParentConstructCallWithMixedArguments()
    {
        $this->assertTrue($this->_model->validate(TestMixedParameters::class));
    }

    public function testValidateIfClassHasArgumentsQtyEqualToParentClass()
    {
        $this->assertTrue($this->_model->validate(\Magento\SomeModule\Model\Three\TestThree::class));
    }

    public function testValidateIfClassHasExtraArgumentInTheParentConstructor()
    {
        $this->_model->validate(\Magento\SomeModule\Model\Four\TestFour::class);
    }

    public function testValidateIfClassHasMissingRequiredArguments()
    {
        $fileName = realpath(__DIR__ . '/../_files/app/code/Magento/SomeModule/Model/Five/TestFive.php');
        $fileName = str_replace('\\', '/', $fileName);
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage(
            'Missed required argument factory in parent::__construct call. File: ' . $fileName
        );
        $this->_model->validate(\Magento\SomeModule\Model\Five\TestFive::class);
    }

    public function testValidateIfClassHasIncompatibleArguments()
    {
        $fileName = realpath(__DIR__ . '/../_files/app/code/Magento/SomeModule/Model/Six/TestSix.php');
        $fileName = str_replace('\\', '/', $fileName);
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage(
            'Incompatible argument type: Required type: \Magento\SomeModule\Model\Proxy. ' .
            'Actual type: \Magento\SomeModule\Model\ElementFactory; File: ' .
            PHP_EOL .
            $fileName
        );
        $this->_model->validate(\Magento\SomeModule\Model\Six\TestSix::class);
    }

    public function testValidateWrongOrderForParentArguments()
    {
        $fileName = realpath(__DIR__) . '/_files/ClassesForConstructorIntegrity.php';
        $fileName = str_replace('\\', '/', $fileName);
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage(
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
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage(
            'Incompatible argument type: Required type: array. ' . 'Actual type: \ClassB; File: ' . PHP_EOL . $fileName
        );
        $this->_model->validate('ClassArgumentWithWrongParentArgumentsType');
    }
}
