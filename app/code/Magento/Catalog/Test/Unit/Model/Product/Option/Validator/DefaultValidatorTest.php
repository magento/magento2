<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Option\Validator;

use Magento\Catalog\Model\Config\Source\Product\Options\Price;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Validator\DefaultValidator;
use Magento\Catalog\Model\ProductOptions\ConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\FormatInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DefaultValidatorTest extends TestCase
{
    /**
     * @var DefaultValidator
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
        $configMock = $this->createMock(ConfigInterface::class);
        $storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $priceConfigMock = new Price($storeManagerMock);
        $this->localeFormatMock = $this->createMock(FormatInterface::class);

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
        $this->validator = new DefaultValidator(
            $configMock,
            $priceConfigMock,
            $this->localeFormatMock
        );
    }

    /**
     * Data provider for testIsValidSuccess
     * @return array
     */
    public function isValidTitleDataProvider()
    {
        $mess = ['option required fields' => 'Missed values for option required fields'];
        return [
            ['option_title', 'name 1.1', 'fixed', 10, new DataObject(['store_id' => 1]), [], true],
            ['option_title', 'name 1.1', 'fixed', 10, new DataObject(['store_id' => 0]), [], true],
            [null, 'name 1.1', 'fixed', 10, new DataObject(['store_id' => 1]), [], true],
            [null, 'name 1.1', 'fixed', 10, new DataObject(['store_id' => 0]), $mess, false],
        ];
    }

    /**
     * @param string $title
     * @param string $type
     * @param string $priceType
     * @param \Magento\Framework\DataObject $product
     * @param array $messages
     * @param bool $result
     * @dataProvider isValidTitleDataProvider
     */
    public function testIsValidTitle($title, $type, $priceType, $price, $product, $messages, $result)
    {
        $methods = ['getTitle', 'getType', 'getPriceType', 'getPrice', '__wakeup', 'getProduct'];
        $valueMock = $this->createPartialMock(Option::class, $methods);
        $valueMock->expects($this->once())->method('getTitle')->will($this->returnValue($title));
        $valueMock->expects($this->any())->method('getType')->will($this->returnValue($type));
        $valueMock->expects($this->once())->method('getPriceType')->will($this->returnValue($priceType));
        $valueMock->expects($this->once())->method('getPrice')->will($this->returnValue($price));
        $valueMock->expects($this->once())->method('getProduct')->will($this->returnValue($product));

        $this->localeFormatMock->expects($this->once())->method('getNumber')->will($this->returnValue($price));

        $this->assertEquals($result, $this->validator->isValid($valueMock));
        $this->assertEquals($messages, $this->validator->getMessages());
    }

    /**
     * Data provider for testIsValidFail
     *
     * @return array
     */
    public function isValidFailDataProvider()
    {
        return [
            [new DataObject(['store_id' => 1])],
            [new DataObject(['store_id' => 0])],
        ];
    }

    /**
     * @param \Magento\Framework\DataObject $product
     * @dataProvider isValidFailDataProvider
     */
    public function testIsValidFail($product)
    {
        $methods = ['getTitle', 'getType', 'getPriceType', 'getPrice', '__wakeup', 'getProduct'];
        $valueMock = $this->createPartialMock(Option::class, $methods);
        $valueMock->expects($this->once())->method('getProduct')->will($this->returnValue($product));
        $valueMock->expects($this->once())->method('getTitle');
        $valueMock->expects($this->any())->method('getType');
        $valueMock->expects($this->once())->method('getPriceType')->will($this->returnValue('some_new_value'));
        $valueMock->expects($this->never())->method('getPrice');
        $messages = [
            'option required fields' => 'Missed values for option required fields',
            'option type' => 'Invalid option type',
            'option values' => 'Invalid option value',
        ];
        $this->assertFalse($this->validator->isValid($valueMock));
        $this->assertEquals($messages, $this->validator->getMessages());
    }

    /**
     * Data provider for testValidationNegativePrice
     * @return array
     */
    public function validationPriceDataProvider()
    {
        return [
            ['option_title', 'name 1.1', 'fixed', -12, new DataObject(['store_id' => 1])],
            ['option_title', 'name 1.1', 'fixed', -12, new DataObject(['store_id' => 0])],
            ['option_title', 'name 1.1', 'fixed', 12, new DataObject(['store_id' => 1])],
            ['option_title', 'name 1.1', 'fixed', 12, new DataObject(['store_id' => 0])]
        ];
    }

    /**
     * @param $title
     * @param $type
     * @param $priceType
     * @param $price
     * @param $product
     * @dataProvider validationPriceDataProvider
     */
    public function testValidationPrice($title, $type, $priceType, $price, $product)
    {
        $methods = ['getTitle', 'getType', 'getPriceType', 'getPrice', '__wakeup', 'getProduct'];
        $valueMock = $this->createPartialMock(Option::class, $methods);
        $valueMock->expects($this->once())->method('getTitle')->will($this->returnValue($title));
        $valueMock->expects($this->exactly(2))->method('getType')->will($this->returnValue($type));
        $valueMock->expects($this->once())->method('getPriceType')->will($this->returnValue($priceType));
        $valueMock->expects($this->once())->method('getPrice')->will($this->returnValue($price));
        $valueMock->expects($this->once())->method('getProduct')->will($this->returnValue($product));

        $this->localeFormatMock->expects($this->once())->method('getNumber')->will($this->returnValue($price));

        $messages = [];
        $this->assertTrue($this->validator->isValid($valueMock));
        $this->assertEquals($messages, $this->validator->getMessages());
    }
}
