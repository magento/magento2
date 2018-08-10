<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\Unit\Controller\Adminhtml\Product\Attribute\Plugin;

class SaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataRequest
     */
    public function testBeforeDispatch($dataRequest, $runTimes)
    {
        $subject = $this->getMock('\Magento\Catalog\Controller\Adminhtml\Product\Attribute\Save', [], [], '', false);
        $request = $this->getMock(
            '\Magento\Framework\App\RequestInterface',
            [
                'getPostValue',
                'setPostValue',
                'getModuleName',
                'setModuleName',
                'getActionName',
                'setActionName',
                'getParam',
                'setParams',
                'getParams',
                'getCookie',
                'isSecure'
            ],
            [],
            '',
            false
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $controller = $objectManager->getObject('\Magento\Swatches\Controller\Adminhtml\Product\Attribute\Plugin\Save');

        $request->expects($this->once())->method('getPostValue')->willReturn($dataRequest);
        $request->expects($this->exactly($runTimes))->method('setPostValue')->willReturn($this->returnSelf());

        $controller->beforeDispatch($subject, $request);
    }

    /**
     * @return array
     */
    public function dataRequest()
    {
        return [
            [
                ['frontend_input' => 'swatch_visual'],
                1
            ],
            [
                ['frontend_input' => 'swatch_text'],
                1
            ],
            [
                ['frontend_input' => 'select'],
                1
            ],
            [
                [],
                0
            ],
            [
                null,
                0
            ],
        ];
    }
}
