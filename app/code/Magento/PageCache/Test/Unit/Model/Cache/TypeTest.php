<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Model\Cache;

use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\PageCache\Model\Cache\Type;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    /** @var Type */
    protected $model;

    /** @var MockObject|ManagerInterface */
    protected $eventManagerMock;

    /** @var MockObject|FrontendPool */
    protected $cacheFrontendPoolMock;

    protected function setUp(): void
    {
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->cacheFrontendPoolMock = $this->getMockBuilder(FrontendPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cacheFrontend = $this->getMockBuilder(FrontendInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->cacheFrontendPoolMock->expects($this->once())
            ->method('get')
            ->willReturn($cacheFrontend);
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Type::class,
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
