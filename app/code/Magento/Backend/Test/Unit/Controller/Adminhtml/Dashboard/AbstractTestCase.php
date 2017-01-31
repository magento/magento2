<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Backend\Test\Unit\Controller\Adminhtml\Dashboard;

/**
 * Abstract test class
 */
class AbstractTestCase extends \PHPUnit_Framework_TestCase
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
        $resultRawMock = $this->getMock(
            'Magento\Framework\Controller\Result\Raw',
            ['setContents'], [], '', false);
        $resultRawFactoryMock = $this->getMock(
            'Magento\Framework\Controller\Result\RawFactory',
            ['create'], [], '', false);
        $layoutFactoryMock = $this->getMock(
            'Magento\Framework\View\LayoutFactory',
            ['create'], [], '', false);
        $layoutMock = $this->getMock('Magento\Framework\View\Layout',
            ['createBlock', 'toHtml'], [], '', false);
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
        $this->assertInstanceOf('Magento\Framework\Controller\Result\Raw', $result);
    }
}
