<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Option\Validator;

use Magento\Catalog\Model\Config\Source\Product\Options\Price;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Validator\Select;
use Magento\Catalog\Model\ProductOptions\ConfigInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SelectTest extends TestCase
{
    /**
     * @var Select
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
        $methods = ['getTitle', 'getType', 'getPriceType', 'getPrice', 'getData'];
        $this->valueMock = $this->createPartialMock(Option::class, $methods);
        $this->validator = new Select(
            $configMock,
            $priceConfigMock,
            $this->localeFormatMock
        );
    }

    /**
     * @param bool $expectedResult
     * @param array $value
     * @dataProvider isValidSuccessDataProvider
     */
    public function testIsValidSuccess($expectedResult, array $value)
    {
        $this->valueMock->expects($this->once())->method('getTitle')->willReturn('option_title');
        $this->valueMock->expects($this->exactly(2))->method('getType')->willReturn('name 1.1');
        $this->valueMock->expects($this->never())->method('getPriceType');
        $this->valueMock->expects($this->never())->method('getPrice');
        $this->valueMock->expects($this->any())->method('getData')->with('values')->willReturn([$value]);
        if (isset($value['price'])) {
            $this->localeFormatMock
                ->expects($this->once())
                ->method('getNumber')
                ->willReturn($value['price']);
        }
        $this->assertEquals($expectedResult, $this->validator->isValid($this->valueMock));
    }

    /**
     * @return array
     */
    public static function isValidSuccessDataProvider()
    {
        return [
            [
                true,
                [
                    'price_type' => 'fixed',
                    'price' => '10',
                    'title' => 'Some Title',
                ]
            ],
            [
                true,
                [
                    'title' => 'Some Title',
                ]
            ],
            [
                true,
                [
                    'title' => 'Some Title',
                    'price_type' => 'fixed',
                    'price' => -10,
                ]
            ],
        ];
    }

    /**
     * @return void
     */
    public function testIsValidateWithInvalidOptionValues()
    {
        $this->valueMock->expects($this->once())->method('getTitle')->willReturn('option_title');
        $this->valueMock->expects($this->exactly(2))->method('getType')->willReturn('name 1.1');
        $this->valueMock->expects($this->never())->method('getPriceType');
        $this->valueMock->expects($this->never())->method('getPrice');
        $this->valueMock
            ->expects($this->once())
            ->method('getData')
            ->with('values')
            ->willReturn('invalid_data');

        $messages = [
            'option values' => 'Invalid option value',
        ];
        $this->assertFalse($this->validator->isValid($this->valueMock));
        $this->assertEquals($messages, $this->validator->getMessages());
    }

    /**
     * @return void
     */
    public function testIsValidateWithEmptyValues()
    {
        $this->valueMock->expects($this->once())->method('getTitle')->willReturn('option_title');
        $this->valueMock->expects($this->exactly(2))->method('getType')->willReturn('name 1.1');
        $this->valueMock->expects($this->never())->method('getPriceType');
        $this->valueMock->expects($this->never())->method('getPrice');
        $this->valueMock->expects($this->any())->method('getData')->with('values')->willReturn([]);
        $messages = [
            'option values' => 'Invalid option value',
        ];
        $this->assertFalse($this->validator->isValid($this->valueMock));
        $this->assertEquals($messages, $this->validator->getMessages());
    }

    /**
     * @param string $priceType
     * @param int $price
     * @param string|null $title
     * @dataProvider isValidateWithInvalidDataDataProvider
     */
    public function testIsValidateWithInvalidData($priceType, $price, $title)
    {
        $value = [
            'price_type' => $priceType,
            'price' => $price,
            'title' => $title,
        ];
        $this->valueMock->expects($this->once())->method('getTitle')->willReturn('option_title');
        $this->valueMock->expects($this->exactly(2))->method('getType')->willReturn('name 1.1');
        $this->valueMock->expects($this->never())->method('getPriceType');
        $this->valueMock->expects($this->never())->method('getPrice');
        $this->valueMock->expects($this->any())->method('getData')->with('values')->willReturn([$value]);
        $this->localeFormatMock->expects($this->any())->method('getNumber')->willReturn($price);
        $messages = [
            'option values' => 'Invalid option value',
        ];
        $this->assertFalse($this->validator->isValid($this->valueMock));
        $this->assertEquals($messages, $this->validator->getMessages());
    }

    /**
     * @return array
     */
    public static function isValidateWithInvalidDataDataProvider()
    {
        return [
            'invalid_price_type' => ['some_value', '10', 'Title'],
            'empty_title' => ['fixed', 10, null]
        ];
    }
}
