<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator\Test\Unit\Constraint;

use Magento\Framework\DataObject;
use Magento\Framework\Validator\Constraint\Property;
use Magento\Framework\Validator\ValidatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test case for \Magento\Framework\Validator\Constraint\Property
 */
class PropertyTest extends TestCase
{
    const PROPERTY_NAME = 'test';

    /**
     * @var Property
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
        $this->_validatorMock = $this->getMockForAbstractClass(ValidatorInterface::class);
        $this->_constraint = new Property(
            $this->_validatorMock,
            self::PROPERTY_NAME
        );
    }

    /**
     * Test getAlias method
     */
    public function testGetAlias()
    {
        $this->assertEmpty($this->_constraint->getAlias());
        $alias = 'foo';
        $constraint = new Property(
            $this->_validatorMock,
            self::PROPERTY_NAME,
            $alias
        );
        $this->assertEquals($alias, $constraint->getAlias());
    }

    /**
     * Test isValid method
     *
     * @dataProvider isValidDataProvider
     *
     * @param mixed $value
     * @param mixed $validateValue
     * @param bool $expectedResult
     * @param array $validatorMessages
     * @param array $expectedMessages
     */
    public function testIsValid(
        $value,
        $validateValue,
        $expectedResult,
        $validatorMessages = [],
        $expectedMessages = []
    ) {
        $this->_validatorMock->expects(
            $this->once()
        )->method(
            'isValid'
        )->with(
            $validateValue
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
                $validatorMessages
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
        return [
            [[self::PROPERTY_NAME => 'Property value', 'foo' => 'Foo value'], 'Property value', true],
            [
                new DataObject([self::PROPERTY_NAME => 'Property value']),
                'Property value',
                true
            ],
            [new \ArrayObject([self::PROPERTY_NAME => 'Property value']), 'Property value', true],
            [
                [self::PROPERTY_NAME => 'Property value', 'foo' => 'Foo value'],
                'Property value',
                false,
                ['Error message 1', 'Error message 2'],
                [self::PROPERTY_NAME => ['Error message 1', 'Error message 2']]
            ],
            [
                ['foo' => 'Foo value'],
                null,
                false,
                ['Error message 1'],
                [self::PROPERTY_NAME => ['Error message 1']]
            ],
            [
                'scalar',
                null,
                false,
                ['Error message 1'],
                [self::PROPERTY_NAME => ['Error message 1']]
            ]
        ];
    }
}
