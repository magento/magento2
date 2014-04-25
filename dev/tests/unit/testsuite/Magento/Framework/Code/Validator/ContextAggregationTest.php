<?php
/**
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
namespace Magento\Framework\Code\Validator;


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

        $this->setExpectedException('\Magento\Framework\Code\ValidationException', $message);
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

        $this->setExpectedException('\Magento\Framework\Code\ValidationException', $message);
        $this->_model->validate('ClassArgumentWithAlreadyInjectedInterface');
    }
}
