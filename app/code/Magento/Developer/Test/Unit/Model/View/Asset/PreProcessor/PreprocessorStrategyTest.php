<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Model\View\Asset\PreProcessor;

use Magento\Developer\Model\Config\Source\WorkflowType;
use Magento\Developer\Model\View\Asset\PreProcessor\FrontendCompilation;
use Magento\Developer\Model\View\Asset\PreProcessor\PreprocessorStrategy;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\PreProcessor\AlternativeSourceInterface;
use Magento\Framework\View\Asset\PreProcessor\Chain;

/**
 * Class PreprocessorStrategyTest
 *
 * @see \Magento\Developer\Model\View\Asset\PreProcessor\PreprocessorStrategy
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PreprocessorStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PreprocessorStrategy
     */
    private $preprocessorStrategy;

    /**
     * @var FrontendCompilation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $frontendCompilationMock;

    /**
     * @var AlternativeSourceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $alternativeSourceMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stateMock;

    /**
     * @var \Magento\Framework\App\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectMangerMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->alternativeSourceMock = $this->getMockBuilder(AlternativeSourceInterface::class)
            ->getMockForAbstractClass();
        $this->frontendCompilationMock = $this->getMockBuilder(FrontendCompilation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();
        $this->stateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectMangerMock = $this->getMockBuilder(\Magento\Framework\App\ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->preprocessorStrategy = (new ObjectManager($this))->getObject(PreprocessorStrategy::class, [
            'alternativeSource' => $this->alternativeSourceMock,
            'frontendCompilation' => $this->frontendCompilationMock,
            'scopeConfig' => $this->scopeConfigMock,
            'state' => $this->stateMock,
        ]);
    }

    /**
     * Run test for process method
     */
    public function testProcessClientSideCompilation()
    {
        $chainMock = $this->getChainMock();

        $this->scopeConfigMock->expects(self::once())
            ->method('getValue')
            ->with(WorkflowType::CONFIG_NAME_PATH)
            ->willReturn(WorkflowType::CLIENT_SIDE_COMPILATION);
        $this->frontendCompilationMock->expects(self::once())
            ->method('process')
            ->with($chainMock);
        $this->alternativeSourceMock->expects(self::never())
            ->method('process');
        $this->stateMock->expects($this->atLeastOnce())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);

        $this->preprocessorStrategy->process($chainMock);
    }

    public function testProcessClientSideCompilationWithDefaultMode()
    {
        $chainMock = $this->getChainMock();

        $this->scopeConfigMock->expects(self::once())
            ->method('getValue')
            ->with(WorkflowType::CONFIG_NAME_PATH)
            ->willReturn(WorkflowType::CLIENT_SIDE_COMPILATION);
        $this->frontendCompilationMock->expects(self::once())
            ->method('process')
            ->with($chainMock);
        $this->alternativeSourceMock->expects(self::never())
            ->method('process');
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_DEFAULT);

        \Magento\Framework\App\ObjectManager::setInstance($this->objectMangerMock);

        $this->preprocessorStrategy->process($chainMock);
    }

    /**
     * Run test for process method
     */
    public function testProcessAlternativeSource()
    {
        $chainMock = $this->getChainMock();

        $this->scopeConfigMock->expects($this->never())
            ->method('getValue')
            ->with(WorkflowType::CONFIG_NAME_PATH)
            ->willReturn('off');
        $this->alternativeSourceMock->expects(self::once())
            ->method('process')
            ->with($chainMock);
        $this->frontendCompilationMock->expects(self::never())
            ->method('process');
        $this->stateMock->expects($this->atLeastOnce())
            ->method('getMode')
            ->willReturn(State::MODE_PRODUCTION);

        $this->preprocessorStrategy->process($chainMock);
    }

    /**
     * @return Chain|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getChainMock()
    {
        $chainMock = $this->getMockBuilder(Chain::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $chainMock;
    }
}
