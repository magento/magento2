<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 * @SuppressWarnings(PHPMD.LongVariable)
 */
namespace Magento\GoogleAdwords\Test\Unit\Model\Validator;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validator\IntUtils;
use Magento\Framework\Validator\Regex;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configurationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_vbFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_vbMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_validatorMock;

    /**
     * @var \Magento\GoogleAdwords\Model\Validator\Factory
     */
    protected $_factory;

    protected function setUp()
    {
        $this->_vbFactoryMock = $this->createPartialMock(
            \Magento\Framework\Validator\UniversalFactory::class,
            ['create']
        );
        $this->_vbMock = $this->createMock(\Magento\Framework\Validator\Builder::class);
        $this->_validatorMock = $this->createMock(\Magento\Framework\Validator\ValidatorInterface::class);

        $objectManager = new ObjectManager($this);
        $this->_factory = $objectManager->getObject(
            \Magento\GoogleAdwords\Model\Validator\Factory::class,
            ['validatorBuilderFactory' => $this->_vbFactoryMock]
        );
    }

    public function testCreateColorValidator()
    {
        $currentColor = 'fff';
        $message = sprintf(
            'Conversion Color value is not valid "%s". Please set hexadecimal 6-digit value.',
            $currentColor
        );

        $this->_vbFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            \Magento\Framework\Validator\Builder::class,
            [
                'constraints' => [
                    [
                        'alias' => 'Regex',
                        'type' => '',
                        'class' => \Magento\Framework\Validator\Regex::class,
                        'options' => [
                            'arguments' => ['pattern' => '/^[0-9a-f]{6}$/i'],
                            'methods' => [
                                [
                                    'method' => 'setMessages',
                                    'arguments' => [
                                        [Regex::NOT_MATCH => $message, Regex::INVALID => $message],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            ]
        )->will(
            $this->returnValue($this->_vbMock)
        );

        $this->_vbMock->expects(
            $this->once()
        )->method(
            'createValidator'
        )->will(
            $this->returnValue($this->_validatorMock)
        );

        $this->assertEquals($this->_validatorMock, $this->_factory->createColorValidator($currentColor));
    }

    public function testCreateConversionIdValidator()
    {
        $conversionId = '123';
        $message = sprintf(
            'Conversion Id value is not valid "%s". Conversion Id should be an integer.',
            $conversionId
        );

        $this->_vbFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            \Magento\Framework\Validator\Builder::class,
            [
                'constraints' => [
                    [
                        'alias' => 'Int',
                        'type' => '',
                        'class' => \Magento\Framework\Validator\IntUtils::class,
                        'options' => [
                            'methods' => [
                                [
                                    'method' => 'setMessages',
                                    'arguments' => [[IntUtils::NOT_INT => $message, IntUtils::INVALID => $message]],
                                ],
                            ],
                        ],
                    ],
                ]
            ]
        )->will(
            $this->returnValue($this->_vbMock)
        );

        $this->_vbMock->expects(
            $this->once()
        )->method(
            'createValidator'
        )->will(
            $this->returnValue($this->_validatorMock)
        );

        $this->assertEquals($this->_validatorMock, $this->_factory->createConversionIdValidator($conversionId));
    }
}
