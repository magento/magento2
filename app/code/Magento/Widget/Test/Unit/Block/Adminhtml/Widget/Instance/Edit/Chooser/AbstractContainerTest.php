<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Test\Unit\Block\Adminhtml\Widget\Instance\Edit\Chooser;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

abstract class AbstractContainerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Event\Manager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Backend\Block\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $themeCollectionMock;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $themeCollectionFactoryMock;

    /**
     * @var \Magento\Theme\Model\Theme|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $themeMock;

    /**
     * @var \Magento\Framework\View\Layout\ProcessorFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $layoutProcessorFactoryMock;

    /**
     * @var \Magento\Framework\View\Model\Layout\Merge|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $layoutMergeMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit\Framework\MockObject\MockObject
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

        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\Manager::class)
            ->setMethods(['dispatch'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->themeCollectionFactoryMock = $this->createPartialMock(
            \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory::class,
            ['create']
        );
        $this->themeCollectionMock = $this->getMockBuilder(\Magento\Theme\Model\ResourceModel\Theme\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItemById'])
            ->getMock();
        $this->themeMock = $this->getMockBuilder(
            \Magento\Theme\Model\Theme::class
        )->disableOriginalConstructor()->getMock();

        $this->layoutProcessorFactoryMock = $this->createPartialMock(
            \Magento\Framework\View\Layout\ProcessorFactory::class,
            ['create']
        );

        $this->layoutMergeMock = $this->getMockBuilder(\Magento\Framework\View\Model\Layout\Merge::class)
            ->setMethods(['addPageHandles', 'load', 'getContainers', 'addHandle'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->escaperMock = $this->createPartialMock(
            \Magento\Framework\Escaper::class,
            ['escapeHtml', 'escapeHtmlAttr']
        );
        $this->escaperMock->expects($this->any())->method('escapeHtmlAttr')->willReturnArgument(0);

        $this->contextMock = $this->getMockBuilder(\Magento\Backend\Block\Context::class)
            ->setMethods(['getEventManager', 'getScopeConfig', 'getEscaper'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->once())->method('getEventManager')->willReturn($this->eventManagerMock);
        $this->contextMock->expects($this->once())->method('getScopeConfig')->willReturn($this->scopeConfigMock);
        $this->contextMock->expects($this->once())->method('getEscaper')->willReturn($this->escaperMock);
    }
}
