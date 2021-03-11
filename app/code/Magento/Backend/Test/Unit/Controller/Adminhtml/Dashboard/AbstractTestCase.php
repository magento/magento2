<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Controller\Adminhtml\Dashboard;

/**
 * Abstract test class
 */
class AbstractTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Assertions for controller execute method
     *
     * @param $controllerName
     * @param $blockName
     */
    protected function assertExecute($controllerName, $blockName)
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $outPut = "data";
        $resultRawMock = $this->createPartialMock(\Magento\Framework\Controller\Result\Raw::class, ['setContents'])
        ;
        $resultRawFactoryMock =
            $this->createPartialMock(\Magento\Framework\Controller\Result\RawFactory::class, ['create']);
        $layoutFactoryMock = $this->createPartialMock(\Magento\Framework\View\LayoutFactory::class, ['create']);
        $layoutMock = $this->createPartialMock(\Magento\Framework\View\Layout::class, ['createBlock', 'toHtml']);
        $layoutFactoryMock->expects($this->once())->method('create')->willReturn($layoutMock);
        $layoutMock->expects($this->once())->method('createBlock')->with($blockName)->willReturnSelf();
        $layoutMock->expects($this->once())->method('toHtml')->willReturn($outPut);
        $resultRawFactoryMock->expects($this->once())->method('create')->willReturn($resultRawMock);
        $resultRawMock->expects($this->once())->method('setContents')->with($outPut)->willReturnSelf();

        $controller = $objectManager->getObject(
            $controllerName,
            [
                'resultRawFactory' => $resultRawFactoryMock,
                'layoutFactory' => $layoutFactoryMock
            ]
        );
        $result = $controller->execute();
        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Raw::class, $result);
    }
}
