<?php declare(strict_types=1);
/***
 * Copyright © Magento, Inc. All rights reserved.
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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RendererFactoryTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var RendererInterface|MockObject
     */
    private $rendererMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $configMock;

    /**
     * @var State|MockObject
     */
    private $stateMock;

    /**
     * @var RendererFactory
     */
    private $model;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->configMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();
        $this->rendererMock = $this->getMockBuilder(RendererInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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
