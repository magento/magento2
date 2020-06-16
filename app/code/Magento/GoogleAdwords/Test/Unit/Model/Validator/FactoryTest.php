<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 * @SuppressWarnings(PHPMD.LongVariable)
 */
declare(strict_types=1);

namespace Magento\GoogleAdwords\Test\Unit\Model\Validator;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validator\Builder;
use Magento\Framework\Validator\IntUtils;
use Magento\Framework\Validator\Regex;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Framework\Validator\ValidatorInterface;
use Magento\GoogleAdwords\Model\Validator\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_configurationMock;

    /**
     * @var MockObject
     */
    protected $_helperMock;

    /**
     * @var MockObject
     */
    protected $_vbFactoryMock;

    /**
     * @var MockObject
     */
    protected $_vbMock;

    /**
     * @var MockObject
     */
    protected $_validatorMock;

    /**
     * @var Factory
     */
    protected $_factory;

    protected function setUp(): void
    {
        $this->_vbFactoryMock = $this->createPartialMock(
            UniversalFactory::class,
            ['create']
        );
        $this->_vbMock = $this->createMock(Builder::class);
        $this->_validatorMock = $this->getMockForAbstractClass(ValidatorInterface::class);

        $objectManager = new ObjectManager($this);
        $this->_factory = $objectManager->getObject(
            Factory::class,
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
            Builder::class,
            [
                'constraints' => [
                    [
                        'alias' => 'Regex',
                        'type' => '',
                        'class' => Regex::class,
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
        )->willReturn(
            $this->_vbMock
        );

        $this->_vbMock->expects(
            $this->once()
        )->method(
            'createValidator'
        )->willReturn(
            $this->_validatorMock
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
            Builder::class,
            [
                'constraints' => [
                    [
                        'alias' => 'Int',
                        'type' => '',
                        'class' => IntUtils::class,
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
        )->willReturn(
            $this->_vbMock
        );

        $this->_vbMock->expects(
            $this->once()
        )->method(
            'createValidator'
        )->willReturn(
            $this->_validatorMock
        );

        $this->assertEquals($this->_validatorMock, $this->_factory->createConversionIdValidator($conversionId));
    }
}
