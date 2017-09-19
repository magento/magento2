<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Option\Validator;

class FileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Option\Validator\File
     */
    protected $validator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $valueMock;

    protected function setUp()
    {
        $configMock = $this->createMock(\Magento\Catalog\Model\ProductOptions\ConfigInterface::class);
        $storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $priceConfigMock = new \Magento\Catalog\Model\Config\Source\Product\Options\Price($storeManagerMock);
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
        $methods = ['getTitle', 'getType', 'getPriceType', 'getPrice', 'getImageSizeX', 'getImageSizeY','__wakeup'];
        $this->valueMock = $this->createPartialMock(\Magento\Catalog\Model\Product\Option::class, $methods);
        $this->validator = new \Magento\Catalog\Model\Product\Option\Validator\File(
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
        $this->valueMock->expects($this->once())->method('getImageSizeX')->will($this->returnValue(10));
        $this->valueMock->expects($this->once())->method('getImageSizeY')->will($this->returnValue(15));
        $this->assertEmpty($this->validator->getMessages());
        $this->assertTrue($this->validator->isValid($this->valueMock));
    }

    public function testIsValidWithNegativeImageSize()
    {
        $this->valueMock->expects($this->once())->method('getTitle')->will($this->returnValue('option_title'));
        $this->valueMock->expects($this->exactly(2))->method('getType')->will($this->returnValue('name 1.1'));
        $this->valueMock->expects($this->once())->method('getPriceType')->will($this->returnValue('fixed'));
        $this->valueMock->expects($this->once())->method('getPrice')->will($this->returnValue(10));
        $this->valueMock->expects($this->once())->method('getImageSizeX')->will($this->returnValue(-10));
        $this->valueMock->expects($this->never())->method('getImageSizeY');
        $messages = [
            'option values' => 'Invalid option value',
        ];
        $this->assertFalse($this->validator->isValid($this->valueMock));
        $this->assertEquals($messages, $this->validator->getMessages());
    }

    public function testIsValidWithNegativeImageSizeY()
    {
        $this->valueMock->expects($this->once())->method('getTitle')->will($this->returnValue('option_title'));
        $this->valueMock->expects($this->exactly(2))->method('getType')->will($this->returnValue('name 1.1'));
        $this->valueMock->expects($this->once())->method('getPriceType')->will($this->returnValue('fixed'));
        $this->valueMock->expects($this->once())->method('getPrice')->will($this->returnValue(10));
        $this->valueMock->expects($this->once())->method('getImageSizeX')->will($this->returnValue(10));
        $this->valueMock->expects($this->once())->method('getImageSizeY')->will($this->returnValue(-10));
        $messages = [
            'option values' => 'Invalid option value',
        ];
        $this->assertFalse($this->validator->isValid($this->valueMock));
        $this->assertEquals($messages, $this->validator->getMessages());
    }
}
