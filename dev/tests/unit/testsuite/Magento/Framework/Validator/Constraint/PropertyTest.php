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

namespace Magento\Framework\Validator\Constraint;

/**
 * Test case for \Magento\Framework\Validator\Constraint\Property
 */
class PropertyTest extends \PHPUnit_Framework_TestCase
{
    const PROPERTY_NAME = 'test';

    /**
     * @var \Magento\Framework\Validator\Constraint\Property
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
        $this->_validatorMock = $this->getMock('Magento\Framework\Validator\ValidatorInterface');
        $this->_constraint = new \Magento\Framework\Validator\Constraint\Property(
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
        $constraint = new \Magento\Framework\Validator\Constraint\Property(
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
        $validatorMessages = array(),
        $expectedMessages = array()
    ) {
        $this->_validatorMock->expects(
            $this->once()
        )->method(
            'isValid'
        )->with(
            $validateValue
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
                $this->returnValue($validatorMessages)
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
        return array(
            array(array(self::PROPERTY_NAME => 'Property value', 'foo' => 'Foo value'), 'Property value', true),
            array(
                new \Magento\Framework\Object(array(self::PROPERTY_NAME => 'Property value')),
                'Property value',
                true
            ),
            array(new \ArrayObject(array(self::PROPERTY_NAME => 'Property value')), 'Property value', true),
            array(
                array(self::PROPERTY_NAME => 'Property value', 'foo' => 'Foo value'),
                'Property value',
                false,
                array('Error message 1', 'Error message 2'),
                array(self::PROPERTY_NAME => array('Error message 1', 'Error message 2'))
            ),
            array(
                array('foo' => 'Foo value'),
                null,
                false,
                array('Error message 1'),
                array(self::PROPERTY_NAME => array('Error message 1'))
            ),
            array(
                'scalar',
                null,
                false,
                array('Error message 1'),
                array(self::PROPERTY_NAME => array('Error message 1'))
            )
        );
    }
}
