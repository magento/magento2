<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Option\Validator;

class SelectTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Option\Validator\Select
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
        $methods = ['getTitle', 'getType', 'getPriceType', 'getPrice', '__wakeup', 'getData'];
        $this->valueMock = $this->createPartialMock(\Magento\Catalog\Model\Product\Option::class, $methods, []);
        $this->validator = new \Magento\Catalog\Model\Product\Option\Validator\Select(
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
    public function isValidSuccessDataProvider()
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
    public function isValidateWithInvalidDataDataProvider()
    {
        return [
            'invalid_price_type' => ['some_value', '10', 'Title'],
            'empty_title' => ['fixed', 10, null]
        ];
    }
}
