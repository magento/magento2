<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Model\Test\Unit\ActionValidator;

use Magento\Catalog\Model\Product;
use Magento\Framework\Model\ActionValidator\RemoveAction;
use Magento\Framework\Registry;
use Magento\Wishlist\Model\Wishlist;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RemoveActionTest extends TestCase
{
    /**
     * @param MockObject $modelToCheck
     * @param string $protectedModel
     * @param bool $secureArea
     * @param bool $expectedResult
     *
     * @dataProvider isAllowedDataProvider
     * @covers \Magento\Framework\Model\ActionValidator\RemoveAction::isAllowed
     * @covers \Magento\Framework\Model\ActionValidator\RemoveAction::getBaseClassName
     */
    public function testIsAllowed($modelToCheck, $protectedModel, $secureArea, $expectedResult)
    {
        if (is_callable($modelToCheck)) {
            $modelToCheck = $modelToCheck($this);
        }
        if (is_callable($protectedModel)) {
            $protectedModel = $protectedModel($this);
        }
        $registryMock = $this->createMock(Registry::class);
        $registryMock->expects($this->once())
            ->method('registry')->with('isSecureArea')->willReturn($secureArea);

        $model = new RemoveAction(
            $registryMock,
            ['class' => $protectedModel]
        );
        $this->assertEquals($expectedResult, $model->isAllowed($modelToCheck));
    }

    /**
     * return array
     */
    public static function isAllowedDataProvider()
    {
        $productMock = static fn(self $testCase) => $testCase->createProductMock();
        $bannerMock = static fn(self $testCase) => $testCase->createWishlistMock()['mock'];
        $bannerMockClass = static fn(self $testCase) => $testCase->createWishlistMock()['class'];

        return [
            [
                'modelToCheck' => $productMock,
                'protectedModel' => 'Model',
                'secureArea' => false,
                'expectedResult' => true,
            ],
            [
                'modelToCheck' => $bannerMock,
                'protectedModel' => $bannerMockClass,
                'secureArea' => false,
                'expectedResult' => false
            ],
            [
                'modelToCheck' => $bannerMock,
                'protectedModel' => $bannerMockClass,
                'secureArea' => true,
                'expectedResult' => true
            ],
        ];
    }

    public function createProductMock()
    {
        return $this->createMock(Product::class);
    }

    public function createWishlistMock()
    {
        $wishlistMock = $this->createMock(Wishlist::class);
        $wishlistMockClass = get_class($wishlistMock);
        return [
            'class' => $wishlistMockClass,
            'mock' => $wishlistMock
        ];
    }
}
