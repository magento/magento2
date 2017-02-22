<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Test\Unit\Block\Adminhtml\Widget\Instance\Edit\Chooser;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

abstract class AbstractContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Event\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Backend\Block\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeCollectionMock;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeCollectionFactoryMock;

    /**
     * @var \Magento\Theme\Model\Theme|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeMock;

    /**
     * @var \Magento\Framework\View\Layout\ProcessorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutProcessorFactoryMock;

    /**
     * @var \Magento\Framework\View\Model\Layout\Merge|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMergeMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaperMock;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->eventManagerMock = $this->getMockBuilder('Magento\Framework\Event\Manager')
            ->setMethods(['dispatch'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder('Magento\Framework\App\Config')
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->themeCollectionFactoryMock = $this->getMock(
            'Magento\Theme\Model\ResourceModel\Theme\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->themeCollectionMock = $this->getMockBuilder('Magento\Theme\Model\ResourceModel\Theme\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['getItemById'])
            ->getMock();
        $this->themeMock = $this->getMockBuilder('Magento\Theme\Model\Theme')->disableOriginalConstructor()->getMock();

        $this->layoutProcessorFactoryMock = $this->getMock(
            'Magento\Framework\View\Layout\ProcessorFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->layoutMergeMock = $this->getMockBuilder('Magento\Framework\View\Model\Layout\Merge')
            ->setMethods(['addPageHandles', 'load', 'getContainers'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->escaperMock = $this->getMock('Magento\Framework\Escaper', ['escapeHtml'], [], '', false);

        $this->contextMock = $this->getMockBuilder('Magento\Backend\Block\Context')
            ->setMethods(['getEventManager', 'getScopeConfig', 'getEscaper'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->once())->method('getEventManager')->willReturn($this->eventManagerMock);
        $this->contextMock->expects($this->once())->method('getScopeConfig')->willReturn($this->scopeConfigMock);
        $this->contextMock->expects($this->once())->method('getEscaper')->willReturn($this->escaperMock);
    }
}
