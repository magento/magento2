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

/**
 * Test case for \Magento\Framework\Validator\Constraint
 */
namespace Magento\Framework\Validator;

class ConstraintTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Validator\Constraint
     */
    protected $_constraint;

    /**
     * @var \Magento\Framework\Validator\ValidatorInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_validatorMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->_validatorMock = $this->getMockBuilder(
            'Magento\Framework\Validator\AbstractValidator'
        )->setMethods(
            array('isValid', 'getMessages')
        )->getMock();
        $this->_constraint = new \Magento\Framework\Validator\Constraint($this->_validatorMock);
    }

    /**
     * Test getAlias method
     */
    public function testGetAlias()
    {
        $this->assertEmpty($this->_constraint->getAlias());
        $alias = 'foo';
        $constraint = new \Magento\Framework\Validator\Constraint($this->_validatorMock, $alias);
        $this->assertEquals($alias, $constraint->getAlias());
    }

    /**
     * Test isValid method
     *
     * @dataProvider isValidDataProvider
     *
     * @param mixed $value
     * @param bool $expectedResult
     * @param array $expectedMessages
     */
    public function testIsValid($value, $expectedResult, $expectedMessages = array())
    {
        $this->_validatorMock->expects(
            $this->once()
        )->method(
            'isValid'
        )->with(
            $value
        )->will(
            $this->returnValue($expectedResult)
        );

        if ($expectedResult) {
            $this->_validatorMock->expects($this->never())->method('getMessages');
        } else {
            $this->_validatorMock->expects(
                $this->once()
            )->method(
                'getMessages'
            )->will(
                $this->returnValue($expectedMessages)
            );
        }

        $this->assertEquals($expectedResult, $this->_constraint->isValid($value));
        $this->assertEquals($expectedMessages, $this->_constraint->getMessages());
    }

    /**
     * Data provider for testIsValid
     *
     * @return array
     */
    public function isValidDataProvider()
    {
        return array(array('test', true), array('test', false, array('foo')));
    }

    /**
     * Check translator was set into wrapped validator
     */
    public function testSetTranslator()
    {
        /** @var \Magento\Framework\Translate\AbstractAdapter $translator */
        $translator = $this->getMockBuilder('Magento\Framework\Translate\AdapterInterface')->getMockForAbstractClass();
        $this->_constraint->setTranslator($translator);
        $this->assertEquals($translator, $this->_validatorMock->getTranslator());
        $this->assertEquals($translator, $this->_constraint->getTranslator());
    }
}
