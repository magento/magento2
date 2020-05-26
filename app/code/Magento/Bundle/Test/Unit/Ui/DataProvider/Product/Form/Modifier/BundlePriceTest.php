<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundlePrice;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Framework\Stdlib\ArrayManager;

class BundlePriceTest extends AbstractModifierTest
{
    /**
     * @return BundlePrice
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(
            BundlePrice::class,
            [
                'locator' => $this->locatorMock,
                'arrayManager' => $this->arrayManagerMock
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testModifyMeta()
    {
        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn(true);
        $this->productMock->expects($this->any())
            ->method('getPriceType')
            ->willReturn(0);
        $priceTypePath = 'bundle-items/children/' . BundlePrice::CODE_PRICE_TYPE;
        $priceTypeConfigPath = $priceTypePath . BundlePrice::META_CONFIG_PATH;
        $pricePath = 'product-details/children/' . ProductAttributeInterface::CODE_PRICE;
        $priceConfigPath = $pricePath . BundlePrice::META_CONFIG_PATH;
        $sourceMeta = [
            'bundle-items' => [
                'children' => [
                    BundlePrice::CODE_PRICE_TYPE => []
                ]
            ]
        ];
        $priceTypeParams = [
            'disabled' => true,
            'valueMap' => [
                'false' => '1',
                'true' => '0'
            ],
            'validation' => [
                'required-entry' => false
            ]
        ];
        $priceTypeMeta = [
            'bundle-items' => [
                'children' => [
                    BundlePrice::CODE_PRICE_TYPE => $priceTypeParams
                ]
            ]
        ];
        $priceParams = [
            'imports' => [
                'disabled' => 'ns = ${ $.ns }, index = ' . BundlePrice::CODE_PRICE_TYPE . ':checked',
                '__disableTmpl' => ['disabled' => false],
            ]
        ];
        $priceMeta = [
            'product-details' => [
                'children' => [
                    BundlePrice::CODE_PRICE_TYPE => []
                ]
            ],
            'bundle-items' => [
                'children' => [
                    ProductAttributeInterface::CODE_PRICE => $priceParams
                ]
            ]
        ];
        $taxParams = [
            'service' => [
                'template' => ''
            ]
        ];

        $this->arrayManagerMock->expects($this->any())
            ->method('findPath')
            ->willReturnMap(
                [
                    [
                        BundlePrice::CODE_PRICE_TYPE,
                        $sourceMeta,
                        null,
                        'children',
                        ArrayManager::DEFAULT_PATH_DELIMITER,
                        $priceTypePath
                    ],
                    [
                        ProductAttributeInterface::CODE_PRICE,
                        $priceTypeMeta,
                        BundlePrice::DEFAULT_GENERAL_PANEL . '/children',
                        'children',
                        ArrayManager::DEFAULT_PATH_DELIMITER,
                        $pricePath
                    ],
                    [
                        BundlePrice::CODE_TAX_CLASS_ID,
                        $priceMeta,
                        null,
                        'children',
                        ArrayManager::DEFAULT_PATH_DELIMITER,
                        $pricePath
                    ],
                    [
                        BundlePrice::CODE_TAX_CLASS_ID,
                        $priceMeta,
                        null,
                        'children',
                        ArrayManager::DEFAULT_PATH_DELIMITER,
                        $pricePath
                    ]
                ]
            );
        $this->arrayManagerMock->expects($this->exactly(4))
            ->method('merge')
            ->willReturnMap(
                [
                    [
                        $priceTypeConfigPath,
                        $sourceMeta,
                        $priceTypeParams,
                        ArrayManager::DEFAULT_PATH_DELIMITER,
                        $priceTypeMeta
                    ],
                    [
                        $priceConfigPath,
                        $priceTypeMeta,
                        $priceParams,
                        ArrayManager::DEFAULT_PATH_DELIMITER,
                        $priceMeta
                    ],
                    [
                        $priceConfigPath,
                        $priceMeta,
                        $priceParams,
                        ArrayManager::DEFAULT_PATH_DELIMITER,
                        $priceMeta
                    ],
                    [
                        $priceConfigPath,
                        $priceMeta,
                        $taxParams,
                        ArrayManager::DEFAULT_PATH_DELIMITER,
                        $priceMeta
                    ]
                ]
            );

        $this->assertSame($priceMeta, $this->getModel()->modifyMeta($sourceMeta));
    }

    public function testModifyData()
    {
        $expectedData = [];
        $this->assertEquals($expectedData, $this->getModel()->modifyData($expectedData));
    }
}
