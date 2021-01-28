<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Test\Unit\ActionValidator;

class RemoveActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param \PHPUnit\Framework\MockObject\MockObject $modelToCheck
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
        $registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $registryMock->expects($this->once())
            ->method('registry')->with('isSecureArea')->willReturn($secureArea);

        $model = new \Magento\Framework\Model\ActionValidator\RemoveAction(
            $registryMock,
            ['class' => $protectedModel]
        );
        $this->assertEquals($expectedResult, $model->isAllowed($modelToCheck));
    }

    /**
     * return array
     */
    public function isAllowedDataProvider()
    {
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $bannerMock = $this->createMock(\Magento\Wishlist\Model\Wishlist::class);

        return [
            [
                'modelToCheck' => $productMock,
                'protectedModel' => 'Model',
                'secureArea' => false,
                'expectedResult' => true,
            ],
            [
                'modelToCheck' => $bannerMock,
                'protectedModel' => get_class($bannerMock),
                'secureArea' => false,
                'expectedResult' => false
            ],
            [
                'modelToCheck' => $bannerMock,
                'protectedModel' => get_class($bannerMock),
                'secureArea' => true,
                'expectedResult' => true
            ],
        ];
    }
}
