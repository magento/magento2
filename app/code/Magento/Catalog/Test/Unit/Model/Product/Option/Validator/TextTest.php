<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Option\Validator;

class TextTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Option\Validator\Text
     */
    protected $validator;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $valueMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $localeFormatMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $configMock = $this->createMock(\Magento\Catalog\Model\ProductOptions\ConfigInterface::class);
        $storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $priceConfigMock = new \Magento\Catalog\Model\Config\Source\Product\Options\Price($storeManagerMock);
        $this->localeFormatMock = $this->createMock(\Magento\Framework\Locale\FormatInterface::class);
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
        $configMock->expects($this->once())->method('getAll')->willReturn($config);
        $methods = ['getTitle', 'getType', 'getPriceType', 'getPrice', '__wakeup', 'getMaxCharacters'];
        $this->valueMock = $this->createPartialMock(\Magento\Catalog\Model\Product\Option::class, $methods);
        $this->validator = new \Magento\Catalog\Model\Product\Option\Validator\Text(
            $configMock,
            $priceConfigMock,
            $this->localeFormatMock
        );
    }

    /**
     * @return void
     */
    public function testIsValidSuccess()
    {
        $this->valueMock->expects($this->once())->method('getTitle')->willReturn('option_title');
        $this->valueMock->expects($this->exactly(2))->method('getType')->willReturn('name 1.1');
        $this->valueMock->method('getPriceType')
            ->willReturn('fixed');
        $this->valueMock->method('getPrice')
            ->willReturn(10);
        $this->valueMock->expects($this->once())->method('getMaxCharacters')->willReturn(10);
        $this->localeFormatMock->expects($this->exactly(2))
            ->method('getNumber')
            ->with($this->equalTo(10))
            ->willReturn(10);
        $this->assertTrue($this->validator->isValid($this->valueMock));
        $this->assertEmpty($this->validator->getMessages());
    }

    /**
     * @return void
     */
    public function testIsValidWithNegativeMaxCharacters()
    {
        $this->valueMock->expects($this->once())->method('getTitle')->willReturn('option_title');
        $this->valueMock->expects($this->exactly(2))->method('getType')->willReturn('name 1.1');
        $this->valueMock->method('getPriceType')
            ->willReturn('fixed');
        $this->valueMock->method('getPrice')
            ->willReturn(10);
        $this->valueMock->expects($this->once())->method('getMaxCharacters')->willReturn(-10);
        $this->localeFormatMock->expects($this->at(0))
            ->method('getNumber')
            ->with($this->equalTo(10))
            ->willReturn(10);
        $this->localeFormatMock
            ->expects($this->at(1))
            ->method('getNumber')
            ->with($this->equalTo(-10))
            ->willReturn(-10);
        $messages = [
            'option values' => 'Invalid option value',
        ];
        $this->assertFalse($this->validator->isValid($this->valueMock));
        $this->assertEquals($messages, $this->validator->getMessages());
    }
}
