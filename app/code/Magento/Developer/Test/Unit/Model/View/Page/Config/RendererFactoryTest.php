<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Model\View\Page\Config;

use Magento\Developer\Model\Config\Source\WorkflowType;
use Magento\Store\Model\ScopeInterface;

class RendererFactoryTest extends \PHPUnit_Framework_TestCase
{
    const RENDERER_TYPE = 'renderer_type';

    const RENDERER_INSTANCE_NAME = 'renderer';

    public function testCreate()
    {
        // Set up mocks
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $configMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $rendererMock = $this->getMockBuilder('Magento\Framework\View\Page\Config\RendererInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $createArgs = [1,2,3];
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->with(self::RENDERER_INSTANCE_NAME, $createArgs)
            ->willReturn($rendererMock);
        $configMock->expects($this->once())
            ->method('getValue')
            ->with(WorkflowType::CONFIG_NAME_PATH, ScopeInterface::SCOPE_STORE)
            ->willReturn(self::RENDERER_TYPE);

        // Set up System Under Test
        $rendererTypes = [
            self::RENDERER_TYPE => self::RENDERER_INSTANCE_NAME
        ];
        $sutArgs = [
            'objectManager' => $objectManagerMock,
            'scopeConfig' => $configMock,
            'rendererTypes' => $rendererTypes
        ];

        $model = $objectManager->getObject('Magento\Developer\Model\View\Page\Config\RendererFactory', $sutArgs);

        // Test
        $this->assertSame($rendererMock, $model->create($createArgs));
    }
}
