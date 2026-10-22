<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Controller\Adminhtml\Cache;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Controller\Adminhtml\Cache\FlushSystem;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\TestCase;

class FlushSystemTest extends TestCase
{
    public function testExecuteCleansRegisteredTypesAndAllMagentoFrontends(): void
    {
        $cacheTypeList = $this->createMock(TypeListInterface::class);
        $cacheTypeList->expects($this->once())->method('getTypes')
            ->willReturn(['config' => null, 'full_page' => null]);
        $cleanedTypes = [];
        $cacheTypeList->expects($this->exactly(2))->method('cleanType')
            ->willReturnCallback(static function ($type) use (&$cleanedTypes): void {
                $cleanedTypes[] = $type;
            });

        $frontend = $this->createMock(FrontendInterface::class);
        $frontend->expects($this->once())->method('clean');
        $frontend->expects($this->never())->method('getBackend');

        $frontendPool = $this->createMock(Pool::class);
        $frontendPool->method('valid')->willReturnOnConsecutiveCalls(true, false);
        $frontendPool->method('current')->willReturn($frontend);

        $events = $this->createMock(ManagerInterface::class);
        $events->expects($this->once())->method('dispatch')
            ->with('adminhtml_cache_flush_system');

        $messages = $this->createMock(MessageManager::class);
        $messages->expects($this->once())->method('addSuccessMessage');

        $redirect = $this->createMock(Redirect::class);
        $redirect->expects($this->once())->method('setPath')
            ->with('adminhtml/*')->willReturnSelf();
        $redirectFactory = $this->createMock(RedirectFactory::class);
        $redirectFactory->method('create')->willReturn($redirect);

        $context = $this->createMock(Context::class);
        $context->method('getEventManager')->willReturn($events);
        $context->method('getMessageManager')->willReturn($messages);
        $context->method('getResultRedirectFactory')->willReturn($redirectFactory);

        $objectManager = new ObjectManager($this);
        $controller = $objectManager->getObject(FlushSystem::class, [
            'context' => $context,
            'cacheTypeList' => $cacheTypeList,
            'cacheState' => $this->createMock(StateInterface::class),
            'cacheFrontendPool' => $frontendPool,
            'resultPageFactory' => $this->createMock(PageFactory::class),
        ]);

        $this->assertSame($redirect, $controller->execute());
        $this->assertSame(['config', 'full_page'], $cleanedTypes);
    }
}
