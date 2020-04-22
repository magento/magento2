<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Widget\Test\Unit\Block\Adminhtml\Widget\Instance\Edit\Chooser;

use Magento\Backend\Block\Context;
use Magento\Framework\App\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Event\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Layout\ProcessorFactory;
use Magento\Framework\View\Model\Layout\Merge;
use Magento\Theme\Model\ResourceModel\Theme\Collection;
use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory;
use Magento\Theme\Model\Theme;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractContainerTest extends TestCase
{
    /**
     * @var Manager|MockObject
     */
    protected $eventManagerMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Collection|MockObject
     */
    protected $themeCollectionMock;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $themeCollectionFactoryMock;

    /**
     * @var Theme|MockObject
     */
    protected $themeMock;

    /**
     * @var ProcessorFactory|MockObject
     */
    protected $layoutProcessorFactoryMock;

    /**
     * @var Merge|MockObject
     */
    protected $layoutMergeMock;

    /**
     * @var Escaper|MockObject
     */
    protected $escaperMock;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->eventManagerMock = $this->getMockBuilder(Manager::class)
            ->setMethods(['dispatch'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(Config::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->themeCollectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->themeCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItemById'])
            ->getMock();
        $this->themeMock = $this->getMockBuilder(
            Theme::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->layoutProcessorFactoryMock = $this->createPartialMock(
            ProcessorFactory::class,
            ['create']
        );

        $this->layoutMergeMock = $this->getMockBuilder(Merge::class)
            ->setMethods(['addPageHandles', 'load', 'getContainers', 'addHandle'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->escaperMock = $this->createPartialMock(
            Escaper::class,
            ['escapeHtml', 'escapeHtmlAttr']
        );
        $this->escaperMock->expects($this->any())->method('escapeHtmlAttr')->willReturnArgument(0);

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->setMethods(['getEventManager', 'getScopeConfig', 'getEscaper'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->once())->method('getEventManager')->willReturn($this->eventManagerMock);
        $this->contextMock->expects($this->once())->method('getScopeConfig')->willReturn($this->scopeConfigMock);
        $this->contextMock->expects($this->once())->method('getEscaper')->willReturn($this->escaperMock);
    }
}
