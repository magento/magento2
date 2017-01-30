<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test case for \Magento\Framework\Validator\Constraint
 */
namespace Magento\Framework\Validator\Test\Unit;

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
            ['isValid', 'getMessages']
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
    public function testIsValid($value, $expectedResult, $expectedMessages = [])
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
        return [['test', true], ['test', false, ['foo']]];
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
