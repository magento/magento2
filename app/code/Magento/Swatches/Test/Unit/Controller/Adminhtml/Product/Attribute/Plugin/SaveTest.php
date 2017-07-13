<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\Unit\Controller\Adminhtml\Product\Attribute\Plugin;

class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider dataRequest
     */
    public function testBeforeDispatch($dataRequest, $runTimes)
    {
        $subject = $this->createMock(\Magento\Catalog\Controller\Adminhtml\Product\Attribute\Save::class);
        $request = $this->createPartialMock(\Magento\Framework\App\RequestInterface::class, [
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
            ]);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $controller = $objectManager->getObject(
            \Magento\Swatches\Controller\Adminhtml\Product\Attribute\Plugin\Save::class
        );

        $request->expects($this->once())->method('getPostValue')->willReturn($dataRequest);
        $request->expects($this->exactly($runTimes))->method('setPostValue')->willReturn($this->returnSelf());

        $controller->beforeDispatch($subject, $request);
    }

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
