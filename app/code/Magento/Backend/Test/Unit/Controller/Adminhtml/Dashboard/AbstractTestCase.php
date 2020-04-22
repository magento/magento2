<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
        $layoutMock = $this->createPartialMock(Layout::class, ['createBlock', 'toHtml']);
        $layoutFactoryMock->expects($this->once())->method('create')->will($this->returnValue($layoutMock));
        $layoutMock->expects($this->once())->method('createBlock')->with($blockName)->will($this->returnSelf());
        $layoutMock->expects($this->once())->method('toHtml')->will($this->returnValue($outPut));
        $resultRawFactoryMock->expects($this->once())->method('create')->will($this->returnValue($resultRawMock));
        $resultRawMock->expects($this->once())->method('setContents')->with($outPut)->will($this->returnSelf());

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
