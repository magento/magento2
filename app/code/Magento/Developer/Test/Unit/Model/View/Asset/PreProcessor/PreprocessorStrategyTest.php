<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Model\View\Asset\PreProcessor;

use Magento\Framework\View\Asset\PreProcessor\Chain;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Developer\Model\Config\Source\WorkflowType;
use Magento\Developer\Model\View\Asset\PreProcessor\FrontendCompilation;
use Magento\Developer\Model\View\Asset\PreProcessor\PreprocessorStrategy;
use Magento\Framework\View\Asset\PreProcessor\AlternativeSourceInterface;

/**
 * Class PreprocessorStrategyTest
 *
 * @see \Magento\Developer\Model\View\Asset\PreProcessor\PreprocessorStrategy
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

        $this->preprocessorStrategy = new PreprocessorStrategy(
            $this->alternativeSourceMock,
            $this->frontendCompilationMock,
            $this->scopeConfigMock
        );
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

        $this->preprocessorStrategy->process($chainMock);
    }

    /**
     * Run test for process method
     */
    public function testProcessAlternativeSource()
    {
        $chainMock = $this->getChainMock();

        $this->scopeConfigMock->expects(self::once())
            ->method('getValue')
            ->with(WorkflowType::CONFIG_NAME_PATH)
            ->willReturn('off');

        $this->alternativeSourceMock->expects(self::once())
            ->method('process')
            ->with($chainMock);

        $this->frontendCompilationMock->expects(self::never())
            ->method('process');

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
