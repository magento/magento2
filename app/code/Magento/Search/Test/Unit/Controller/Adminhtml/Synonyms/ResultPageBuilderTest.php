<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Controller\Adminhtml\Synonyms;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Search\EngineResolverInterface;
use Magento\Framework\Search\SearchEngine\ConfigInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Search\Controller\Adminhtml\Synonyms\ResultPageBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ResultPageBuilderTest extends TestCase
{
    /** @var ResultPageBuilder */
    private $model;

    /** @var PageFactory|MockObject */
    private $resultPageFactoryMock;

    /** @var EngineResolverInterface|MockObject */
    private $engineResolverMock;

    /** @var ConfigInterface|MockObject */
    private $searchFeatureConfigMock;

    /** @var ManagerInterface|MockObject */
    private $messageManagerMock;

    protected function setUp(): void
    {
        $this->resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->engineResolverMock = $this->getMockBuilder(EngineResolverInterface::class)
            ->getMockForAbstractClass();
        $this->searchFeatureConfigMock = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();
        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->model = new ResultPageBuilder(
            $this->resultPageFactoryMock,
            $this->engineResolverMock,
            $this->searchFeatureConfigMock,
            $this->messageManagerMock
        );
    }

    public function testBuild()
    {
        $currentEngine = 'current_engine';

        $this->engineResolverMock->expects($this->once())
            ->method('getCurrentSearchEngine')
            ->willReturn($currentEngine);

        $this->searchFeatureConfigMock->expects($this->once())
            ->method('isFeatureSupported')
            ->with(ConfigInterface::SEARCH_ENGINE_FEATURE_SYNONYMS, $currentEngine)
            ->willReturn(true);

        $this->messageManagerMock->expects($this->never())
            ->method('addNoticeMessage');

        $resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultPageMock);

        $this->assertEquals($resultPageMock, $this->model->build());
    }

    public function testBuildWithDisabledEngine()
    {
        $currentEngine = 'current_engine';

        $this->engineResolverMock->expects($this->once())
            ->method('getCurrentSearchEngine')
            ->willReturn($currentEngine);

        $this->searchFeatureConfigMock->expects($this->once())
            ->method('isFeatureSupported')
            ->with(ConfigInterface::SEARCH_ENGINE_FEATURE_SYNONYMS, $currentEngine)
            ->willReturn(false);

        $this->messageManagerMock->expects($this->once())
            ->method('addNoticeMessage');

        $resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultPageMock);

        $this->assertEquals($resultPageMock, $this->model->build());
    }
}
