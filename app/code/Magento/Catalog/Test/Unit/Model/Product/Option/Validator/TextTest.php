<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Option\Validator;

use Magento\Catalog\Model\Config\Source\Product\Options\Price;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Validator\Text;
use Magento\Catalog\Model\ProductOptions\ConfigInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TextTest extends TestCase
{
    /**
     * @var Text
     */
    protected $validator;

    /**
     * @var MockObject
     */
    protected $valueMock;

    /**
     * @var MockObject
     */
    protected $localeFormatMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $configMock = $this->getMockForAbstractClass(ConfigInterface::class);
        $storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $priceConfigMock = new Price($storeManagerMock);
        $this->localeFormatMock = $this->getMockForAbstractClass(FormatInterface::class);
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
        $methods = ['getTitle', 'getType', 'getPriceType', 'getPrice', 'getMaxCharacters'];
        $this->valueMock = $this->createPartialMock(Option::class, $methods);
        $this->validator = new Text(
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
            ->with(10)
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
            ->with(10)
            ->willReturn(10);
        $this->localeFormatMock
            ->expects($this->at(1))
            ->method('getNumber')
            ->with(-10)
            ->willReturn(-10);
        $messages = [
            'option values' => 'Invalid option value',
        ];
        $this->assertFalse($this->validator->isValid($this->valueMock));
        $this->assertEquals($messages, $this->validator->getMessages());
    }
}
