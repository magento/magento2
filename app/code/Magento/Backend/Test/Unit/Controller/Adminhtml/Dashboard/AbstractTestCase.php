<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Controller\Adminhtml\Dashboard;

use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutFactory;
use PHPUnit\Framework\TestCase;

/**
 * Abstract test class
 */
class AbstractTestCase extends TestCase
{
    /**
     * Assertions for controller execute method
     *
     * @param $controllerName
     * @param $blockName
     */
    protected function assertExecute($controllerName, $blockName)
    {
        $objectManager = new ObjectManager($this);
        $outPut = "data";
        $resultRawMock = $this->createPartialMock(Raw::class, ['setContents']);
        $resultRawFactoryMock =
            $this->createPartialMock(RawFactory::class, ['create']);
        $layoutFactoryMock = $this->createPartialMock(LayoutFactory::class, ['create']);
        $layoutMock = $this->getMockBuilder(Layout::class)
            ->addMethods(['toHtml'])
            ->onlyMethods(['createBlock'])
            ->disableOriginalConstructor()
            ->getMock();
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
        $this->assertInstanceOf(Raw::class, $result);
    }
}
