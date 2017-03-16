<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Test\Unit\Validator;

require_once __DIR__ . '/_files/ClassesForContextAggregation.php';
class ContextAggregationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Code\Validator\ContextAggregation
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_fixturePath;

    protected function setUp()
    {
        $this->_model = new \Magento\Framework\Code\Validator\ContextAggregation();
        $this->_fixturePath = str_replace('\\', '/', realpath(__DIR__) . '/_files/ClassesForContextAggregation.php');
    }

    public function testClassArgumentAlreadyInjectedIntoContext()
    {
        $message = 'Incorrect dependency in class ClassArgumentAlreadyInjectedInContext in ' .
            $this->_fixturePath .
            PHP_EOL .
            '\ClassFirst already exists in context object';

        $this->setExpectedException(\Magento\Framework\Exception\ValidatorException::class, $message);
        $this->_model->validate('ClassArgumentAlreadyInjectedInContext');
    }

    public function testClassArgumentWithInterfaceImplementation()
    {
        $this->assertTrue($this->_model->validate('ClassArgumentWithInterfaceImplementation'));
    }

    public function testClassArgumentWithInterface()
    {
        $this->assertTrue($this->_model->validate('ClassArgumentWithInterface'));
    }

    public function testClassArgumentWithAlreadyInjectedInterface()
    {
        $message = 'Incorrect dependency in class ClassArgumentWithAlreadyInjectedInterface in ' .
            $this->_fixturePath .
            PHP_EOL .
            '\\InterfaceFirst already exists in context object';

        $this->setExpectedException(\Magento\Framework\Exception\ValidatorException::class, $message);
        $this->_model->validate('ClassArgumentWithAlreadyInjectedInterface');
    }
}
