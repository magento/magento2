<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Test\Unit\ActionValidator;

class RemoveActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $modelToCheck
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
        $registryMock = $this->getMock('\Magento\Framework\Registry', [], [], '', false);
        $registryMock->expects($this->once())
            ->method('registry')->with('isSecureArea')->will($this->returnValue($secureArea));

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
        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $bannerMock = $this->getMock('\Magento\Wishlist\Model\Wishlist', [], [], '', false);

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
