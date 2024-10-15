<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test case for \Magento\Framework\Validator\Constraint
 */
namespace Magento\Framework\Validator\Test\Unit;

use Magento\Framework\Translate\AbstractAdapter;
use Magento\Framework\Translate\AdapterInterface;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Framework\Validator\Constraint;
use Magento\Framework\Validator\ValidatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConstraintTest extends TestCase
{
    /**
     * @var Constraint
     */
    protected $_constraint;

    /**
     * @var ValidatorInterface|MockObject
     */
    protected $_validatorMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->_validatorMock = $this->getMockBuilder(
            AbstractValidator::class
        )->onlyMethods(
            ['isValid', 'getMessages']
        )->getMock();
        $this->_constraint = new Constraint($this->_validatorMock);
    }

    /**
     * Test getAlias method
     */
    public function testGetAlias()
    {
        $this->assertEmpty($this->_constraint->getAlias());
        $alias = 'foo';
        $constraint = new Constraint($this->_validatorMock, $alias);
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
        )->willReturn(
            $expectedResult
        );

        if ($expectedResult) {
            $this->_validatorMock->expects($this->never())->method('getMessages');
        } else {
            $this->_validatorMock->expects(
                $this->once()
            )->method(
                'getMessages'
            )->willReturn(
                $expectedMessages
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
    public static function isValidDataProvider()
    {
        return [['test', true], ['test', false, ['foo']]];
    }

    /**
     * Check translator was set into wrapped validator
     */
    public function testSetTranslator()
    {
        /** @var AbstractAdapter $translator */
        $translator = $this->getMockBuilder(
            AdapterInterface::class
        )->getMockForAbstractClass();
        $this->_constraint->setTranslator($translator);
        $this->assertEquals($translator, $this->_validatorMock->getTranslator());
        $this->assertEquals($translator, $this->_constraint->getTranslator());
    }
}
