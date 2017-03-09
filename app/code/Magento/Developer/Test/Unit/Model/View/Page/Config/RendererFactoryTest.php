<?php
/***
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Model\View\Page\Config;

use Magento\Developer\Model\Config\Source\WorkflowType;
use Magento\Developer\Model\View\Page\Config\RendererFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Page\Config\RendererInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class RendererFactoryTest
 */
class RendererFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var RendererInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rendererMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stateMock;

    /**
     * @var RendererFactory
     */
    private $model;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->configMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();
        $this->rendererMock = $this->getMockBuilder(RendererInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->with(State::class)
            ->willReturn($this->stateMock);

        $this->model = (new ObjectManagerHelper($this))->getObject(RendererFactory::class, [
            'objectManager' => $this->objectManagerMock,
            'scopeConfig' => $this->configMock,
            'rendererTypes' => ['renderer_type' => 'renderer'],
        ]);
    }

    public function testCreate()
    {
        $this->stateMock->expects($this->once())
            ->method('getMode');
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('renderer', [])
            ->willReturn($this->rendererMock);
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(WorkflowType::CONFIG_NAME_PATH, ScopeInterface::SCOPE_STORE)
            ->willReturn('renderer_type');

        $this->assertSame($this->rendererMock, $this->model->create());
    }
}
