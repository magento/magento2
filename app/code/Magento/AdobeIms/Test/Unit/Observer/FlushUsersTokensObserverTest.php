<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Test\Unit\Observer;

use Magento\AdobeIms\Model\FlushUserTokens;
use Magento\AdobeIms\Observer\FlushUsersTokensObserver;
use Magento\Authorization\Model\Role;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Flush users tokens observer tests
 */
class FlushUsersTokensObserverTest extends TestCase
{
    /** @var FlushUserTokens|MockObject */
    protected $flushUserTokens;

    /** @var FlushUsersTokensObserver */
    protected $model;

    protected function setUp(): void
    {
        $this->flushUserTokens = $this->createMock(FlushUserTokens::class);
        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            FlushUsersTokensObserver::class,
            [
                'flushUserTokens' => $this->flushUserTokens
            ]
        );
    }

    /**
     * Test flush tokens observer
     */
    public function testFlushUsersTokensObserver(): void
    {
        /** @var Observer|MockObject $eventObserverMock */
        $eventObserverMock = $this->createMock(Observer::class);
        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->expects($this->once())->method("getParam")->willReturn(["Magento_AnyModule::anything"]);
        $roleMock = $this->createMock(Role::class);
        $roleMock->expects($this->once())->method("getRoleUsers")->willReturn([1,2,3]);
        $eventObserverMock->expects($this->exactly(2))->method("getDataByKey")
            ->will($this->returnValueMap([["request", $requestMock],["object", $roleMock]]));
        $this->flushUserTokens->expects($this->exactly(3))->method("execute")->willReturnSelf();
        $this->model->execute($eventObserverMock);
    }
}
