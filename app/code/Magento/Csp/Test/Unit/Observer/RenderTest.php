<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Observer;

use Magento\Csp\Api\CspRendererInterface;
use Magento\Csp\Observer\Render;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test for \Magento\Csp\Observer\Render
 */
class RenderTest extends TestCase
{
    /**
     * Check if the render method is called
     */
    public function testExecuteExpectsRenderCalled()
    {
        $eventMock = $this->createMock(Event::class);
        $responseMock = $this->createMock(ResponseHttp::class);
        $eventMock->expects($this->once())
            ->method('getData')
            ->with('response')
            ->willReturn($responseMock);

        /** @var MockObject|Observer $eventObserverMock */
        $eventObserverMock = $this->createMock(Observer::class);
        $eventObserverMock->expects($this->once())->method('getEvent')->willReturn($eventMock);

        $cspRendererMock = $this->getMockForAbstractClass(CspRendererInterface::class);
        $cspRendererMock->expects($this->once())->method('render');

        $objectManagerHelper = new ObjectManager($this);
        /** @var Render $renderObserver */
        $renderObserver = $objectManagerHelper->getObject(
            Render::class,
            ['cspRenderer' => $cspRendererMock]
        );
        $renderObserver->execute($eventObserverMock);
    }
}
