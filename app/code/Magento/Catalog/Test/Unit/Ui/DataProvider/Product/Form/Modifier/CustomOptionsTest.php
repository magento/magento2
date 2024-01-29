<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Config\Source\Product\Options\Price as ProductOptionsPrice;
use Magento\Catalog\Model\Product\Option as ProductOption;
use Magento\Catalog\Model\ProductOptions\ConfigInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class CustomOptionsTest extends AbstractModifierTest
{
    /**
     * @var ConfigInterface|MockObject
     */
    protected $productOptionsConfigMock;

    /**
     * @var ProductOptionsPrice|MockObject
     */
    protected $productOptionsPriceMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var StoreInterface|MockObject
     */
    protected $storeMock;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrency;

    protected function setUp(): void
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
            ->addMethods(['getBaseCurrency'])
            ->getMockForAbstractClass();
        $this->priceCurrency = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

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

        $meta = $this->getModel()->modifyMeta([]);

        $this->assertArrayHasKey(CustomOptions::GROUP_CUSTOM_OPTIONS_NAME, $meta);

        $buttonAdd = $meta['custom_options']['children']['container_header']['children']['button_add'];
        $buttonAddTargetName = $buttonAdd['arguments']['data']['config']['actions'][0]['targetName'];
        $expectedTargetName = '${ $.ns }.${ $.ns }.' . CustomOptions::GROUP_CUSTOM_OPTIONS_NAME
            . '.' . CustomOptions::GRID_OPTIONS_NAME;

        $this->assertEquals($expectedTargetName, $buttonAddTargetName);
    }

    /**
     * Tests if Compatible File Extensions is required when Option Type "File" is selected in Customizable Options.
     */
    public function testFileExtensionRequired()
    {
        $this->productOptionsConfigMock->expects($this->once())
            ->method('getAll')
            ->willReturn([]);

        $meta = $this->getModel()->modifyMeta([]);

        $config = $meta['custom_options']['children']['options']['children']['record']['children']['container_option']
        ['children']['container_type_static']['children']['file_extension']['arguments']['data']['config'];

        $scope = $config['dataScope'];
        $required = $config['validation']['required-entry'];

        $this->assertEquals(CustomOptions::FIELD_FILE_EXTENSION_NAME, $scope);
        $this->assertTrue($required);
    }

    /**
     * Get ProductOption mock object
     *
     * @param array $data
     * @param array $values
     * @return \Magento\Catalog\Model\Product\Option|MockObject
     */
    protected function getProductOptionMock(array $data, array $values = [])
    {
        /** @var ProductOption|MockObject $productOptionMock */
        $productOptionMock = $this->getMockBuilder(ProductOption::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValues'])
            ->getMock();

        $productOptionMock->setData($data);
        $productOptionMock->expects($this->any())
            ->method('getValues')
            ->willReturn($values);

        return $productOptionMock;
    }
}
