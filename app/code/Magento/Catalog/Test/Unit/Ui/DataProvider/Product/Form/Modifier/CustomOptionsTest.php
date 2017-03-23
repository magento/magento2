<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use Magento\Catalog\Model\ProductOptions\ConfigInterface;
use Magento\Catalog\Model\Config\Source\Product\Options\Price as ProductOptionsPrice;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Model\Product\Option as ProductOption;

/**
 * Class CustomOptionsTest
 */
class CustomOptionsTest extends AbstractModifierTest
{
    /**
     * @var ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productOptionsConfigMock;

    /**
     * @var ProductOptionsPrice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productOptionsPriceMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrency;

    protected function setUp()
    {
        parent::setUp();
        $this->productOptionsConfigMock = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();
        $this->productOptionsPriceMock = $this->getMockBuilder(ProductOptionsPrice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getBaseCurrency'])
            ->getMockForAbstractClass();
        $this->priceCurrency = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())
            ->method('getBaseCurrency')
            ->willReturn($this->priceCurrency);
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(CustomOptions::class, [
            'locator' => $this->locatorMock,
            'productOptionsConfig' => $this->productOptionsConfigMock,
            'productOptionsPrice' => $this->productOptionsPriceMock,
            'storeManager' => $this->storeManagerMock
        ]);
    }

    public function testModifyData()
    {
        $productId = 111;

        $originalData = [
            $productId => [
                CustomOptions::DATA_SOURCE_DEFAULT => [
                    'title' => 'original'
                ]
            ]
        ];

        $options = [
            $this->getProductOptionMock(['title' => 'option1', 'store_title' => 'Option Store Title']),
            $this->getProductOptionMock(
                ['title' => 'option2', 'store_title' => null],
                [
                    $this->getProductOptionMock(['title' => 'value1', 'store_title' => 'Option Value Store Title']),
                    $this->getProductOptionMock(['title' => 'value2', 'store_title' => null])
                ]
            )
        ];

        $resultData = [
            $productId => [
                CustomOptions::DATA_SOURCE_DEFAULT => [
                    CustomOptions::FIELD_TITLE_NAME => 'original',
                    CustomOptions::FIELD_ENABLE => 1,
                    CustomOptions::GRID_OPTIONS_NAME => [
                        [
                            CustomOptions::FIELD_TITLE_NAME => 'option1',
                            CustomOptions::FIELD_STORE_TITLE_NAME => 'Option Store Title',
                            CustomOptions::FIELD_IS_USE_DEFAULT => false
                        ], [
                            CustomOptions::FIELD_TITLE_NAME => 'option2',
                            CustomOptions::FIELD_STORE_TITLE_NAME => null,
                            CustomOptions::FIELD_IS_USE_DEFAULT => true,
                            CustomOptions::GRID_TYPE_SELECT_NAME => [
                                [
                                    CustomOptions::FIELD_TITLE_NAME => 'value1',
                                    CustomOptions::FIELD_STORE_TITLE_NAME => 'Option Value Store Title',
                                    CustomOptions::FIELD_IS_USE_DEFAULT => false
                                ], [
                                    CustomOptions::FIELD_TITLE_NAME => 'value2',
                                    CustomOptions::FIELD_STORE_TITLE_NAME => null,
                                    CustomOptions::FIELD_IS_USE_DEFAULT => true
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn($productId);
        $this->productMock->expects($this->once())
            ->method('getOptions')
            ->willReturn($options);

        $this->assertSame($resultData, $this->getModel()->modifyData($originalData));
    }

    public function testModifyMeta()
    {
        $this->priceCurrency->expects($this->any())
            ->method('getCurrencySymbol')
            ->willReturn('$');
        $this->productOptionsConfigMock->expects($this->once())
            ->method('getAll')
            ->willReturn([]);

        $this->assertArrayHasKey(CustomOptions::GROUP_CUSTOM_OPTIONS_NAME, $this->getModel()->modifyMeta([]));
    }

    /**
     * Get ProductOption mock object
     *
     * @param array $data
     * @param array $values
     * @return \Magento\Catalog\Model\Product\Option|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getProductOptionMock(array $data, array $values = [])
    {
        /** @var ProductOption|\PHPUnit_Framework_MockObject_MockObject $productOptionMock */
        $productOptionMock = $this->getMockBuilder(ProductOption::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValues'])
            ->getMock();

        $productOptionMock->setData($data);
        $productOptionMock->expects($this->any())
            ->method('getValues')
            ->willReturn($values);

        return $productOptionMock;
    }
}
