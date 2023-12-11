<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Model\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    /**
     * @dataProvider dataRoles
     */
    public function testAfterGetMediaAttributes($productType, $hasKey)
    {
        $objectManager = new ObjectManager($this);
        $productMock = $this->createPartialMock(Product::class, ['getTypeId']);
        $roleMock = $this->createMock(Attribute::class);

        $imageRolesArray = [
            'image' => $roleMock,
            'small_image' => $roleMock,
            'thumbnail' => $roleMock,
            'swatch_image' => $roleMock,
        ];

        $plugin = $objectManager->getObject(\Magento\Swatches\Model\Plugin\Product::class);

        $productMock->expects($this->atLeastOnce())->method('getTypeId')->willReturn($productType);

        $result = $plugin->afterGetMediaAttributes($productMock, $imageRolesArray);

        if ($hasKey) {
            $this->assertArrayHasKey('swatch_image', $result);
        } else {
            $this->assertArrayNotHasKey('swatch_image', $result);
        }
    }

    /**
     * @return array
     */
    public function dataRoles()
    {
        return [
            [
                'configurable',
                false,
            ],
            [
                'simple',
                true,
            ],
            [
                'virtual',
                true,
            ],
            [
                'bundle',
                false,
            ],
            [
                'grouped',
                false,
            ],
            [
                'downloadable',
                false,
            ],
            [
                'custom_product_type',
                false,
            ],
        ];
    }
}
