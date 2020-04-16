<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Test\Unit\Model\Cache;

class TypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\PageCache\Model\Cache\Type */
    protected $model;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Framework\Event\ManagerInterface */
    protected $eventManagerMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Framework\App\Cache\Type\FrontendPool */
    protected $cacheFrontendPoolMock;

    protected function setUp(): void
    {
        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheFrontendPoolMock = $this->getMockBuilder(\Magento\Framework\App\Cache\Type\FrontendPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cacheFrontend = $this->getMockBuilder(\Magento\Framework\Cache\FrontendInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheFrontendPoolMock->expects($this->once())
            ->method('get')
            ->willReturn($cacheFrontend);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\PageCache\Model\Cache\Type::class,
            [
                'eventManager' => $this->eventManagerMock,
                'cacheFrontendPool' => $this->cacheFrontendPoolMock,
            ]
        );
    }

    public function testClean()
    {
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with('adminhtml_cache_refresh_type');

        $this->model->clean();
    }
}
