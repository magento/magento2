<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Option\Validator;

class TextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Option\Validator\Text
     */
    protected $validator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $valueMock;

    protected function setUp()
    {
        $configMock = $this->getMock(\Magento\Catalog\Model\ProductOptions\ConfigInterface::class);
        $priceConfigMock = new \Magento\Catalog\Model\Config\Source\Product\Options\Price();
        $config = [
            [
                'label' => 'group label 1',
                'types' => [
                    [
                        'label' => 'label 1.1',
                        'name' => 'name 1.1',
                        'disabled' => false,
                    ],
                ],
            ],
            [
                'label' => 'group label 2',
                'types' => [
                    [
                        'label' => 'label 2.2',
                        'name' => 'name 2.2',
                        'disabled' => true,
                    ],
                ]
            ],
        ];
        $configMock->expects($this->once())->method('getAll')->will($this->returnValue($config));
        $methods = ['getTitle', 'getType', 'getPriceType', 'getPrice', '__wakeup', 'getMaxCharacters'];
        $this->valueMock = $this->getMock(\Magento\Catalog\Model\Product\Option::class, $methods, [], '', false);
        $this->validator = new \Magento\Catalog\Model\Product\Option\Validator\Text(
            $configMock,
            $priceConfigMock
        );
    }

    public function testIsValidSuccess()
    {
        $this->valueMock->expects($this->once())->method('getTitle')->will($this->returnValue('option_title'));
        $this->valueMock->expects($this->exactly(2))->method('getType')->will($this->returnValue('name 1.1'));
        $this->valueMock->expects($this->once())->method('getPriceType')->will($this->returnValue('fixed'));
        $this->valueMock->expects($this->once())->method('getPrice')->will($this->returnValue(10));
        $this->valueMock->expects($this->once())->method('getMaxCharacters')->will($this->returnValue(10));
        $this->assertTrue($this->validator->isValid($this->valueMock));
        $this->assertEmpty($this->validator->getMessages());
    }

    public function testIsValidWithNegativeMaxCharacters()
    {
        $this->valueMock->expects($this->once())->method('getTitle')->will($this->returnValue('option_title'));
        $this->valueMock->expects($this->exactly(2))->method('getType')->will($this->returnValue('name 1.1'));
        $this->valueMock->expects($this->once())->method('getPriceType')->will($this->returnValue('fixed'));
        $this->valueMock->expects($this->once())->method('getPrice')->will($this->returnValue(10));
        $this->valueMock->expects($this->once())->method('getMaxCharacters')->will($this->returnValue(-10));
        $messages = [
            'option values' => 'Invalid option value',
        ];
        $this->assertFalse($this->validator->isValid($this->valueMock));
        $this->assertEquals($messages, $this->validator->getMessages());
    }
}
