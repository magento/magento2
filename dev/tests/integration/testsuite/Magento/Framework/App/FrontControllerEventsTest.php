<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App;

use Magento\Framework\Event\ManagerInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Request as TestHttpRequest;
use Magento\TestFramework\Response;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppIsolation enabled
 */
class FrontControllerEventsTest extends TestCase
{
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var TestHttpRequest
     */
    private $request;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = ObjectManager::getInstance();
        $this->setupEventManagerSpy();
        $this->eventManager = $this->objectManager->get(ManagerInterface::class);
        $this->request = $this->objectManager->get(TestHttpRequest::class);
    }

    /**
     * Test if frontend controller dispatches events
     *
     * @magentoAppArea frontend
     *
     * @return void
     */
    public function testFrontendControllerDispatchesEvents(): void
    {
        $this->setupEventManagerSpy();

        /** @var FrontControllerInterface $frontController */
        $frontController = ObjectManager::getInstance()->create(FrontControllerInterface::class);
        $this->configureRequestForAction('cms', 'index', 'index');
        $frontController->dispatch($this->request);

        $this->assertPreAndPostDispatchEventsAreDispatched();
    }

    /**
     * Test if no dispatch flag prevents dispatching action
     *
     * @magentoAppArea frontend
     *
     * @return void
     */
    public function testSettingTheNoDispatchActionFlagProhibitsExecuteAndPostdispatchEvents(): void
    {
        $this->setupEventManagerSpy();

        /** @var FrontControllerInterface $frontController */
        $frontController = ObjectManager::getInstance()->create(FrontControllerInterface::class);
        $this->configureRequestForAction('cms', 'index', 'index');

        /** @var ActionFlag $actionFlag */
        $actionFlag = ObjectManager::getInstance()->get(ActionFlag::class);
        $actionFlag->set('', ActionInterface::FLAG_NO_DISPATCH, true);

        $result1 = $frontController->dispatch($this->request);
        $this->assertTrue($result1 instanceof Response, 'Action was dispatched!');
        $this->assertPreDispatchEventsAreDispatched();
    }

    /**
     * Prepare spy on event manager
     *
     * @return void
     */
    public function setupEventManagerSpy(): void
    {
        $eventManager = $this->objectManager->get(ManagerInterface::class);
        $eventManagerSpy = new class($eventManager) implements ManagerInterface {
            /**
             * @var ManagerInterface
             */
            private $delegate;

            /**
             * @var array[];
             */
            private $dispatchedEvents;

            /**
             * @param ManagerInterface $delegate
             */
            public function __construct(ManagerInterface $delegate)
            {
                $this->delegate = $delegate;
            }

            public function dispatch($eventName, array $data = [])
            {
                $this->dispatchedEvents[$eventName][] = [$eventName, $data];
                $this->delegate->dispatch($eventName, $data);
            }

            public function spyOnDispatchedEvent(string $eventName): array
            {
                return $this->dispatchedEvents[$eventName] ?? [];
            }
        };

        $this->objectManager->addSharedInstance($eventManagerSpy, get_class($eventManager));
    }

    /**
     * Check if event was dispatched exactly as many times as expected
     *
     * @param string $eventName
     * @param int $expectedCount
     *
     * @return void
     */
    private function assertEventDispatchCount(string $eventName, int $expectedCount): void
    {
        $message = sprintf('Event %s was expected to be dispatched %d time(s).', $eventName, $expectedCount);
        $this->assertCount($expectedCount, $this->eventManager->spyOnDispatchedEvent($eventName), $message);
    }

    /**
     * Prepare request for test action
     *
     * @param string $route
     * @param string $actionPath
     * @param string $actionName
     *
     * @return void
     */
    private function configureRequestForAction(string $route, string $actionPath, string $actionName): void
    {
        $request = $this->request;

        $request->setRouteName($route);
        $request->setControllerName($actionPath);
        $request->setActionName($actionName);
        $request->setDispatched();
        $request->setRequestUri("$route/$actionPath/$actionName");
    }

    /**
     * Check events dispatched before and after execute
     *
     * @return void
     */
    private function assertPreAndPostDispatchEventsAreDispatched(): void
    {
        $this->assertEventDispatchCount('controller_action_predispatch', 1);
        $this->assertEventDispatchCount('controller_action_predispatch_cms', 1);
        $this->assertEventDispatchCount('controller_action_predispatch_cms_index_index', 1);
        $this->assertEventDispatchCount('controller_action_postdispatch_cms_index_index', 1);
        $this->assertEventDispatchCount('controller_action_postdispatch_cms', 1);
        $this->assertEventDispatchCount('controller_action_postdispatch', 1);
    }

    /**
     * Check events are dispatched only before execute
     *
     * @return void
     */
    private function assertPreDispatchEventsAreDispatched(): void
    {
        $this->assertEventDispatchCount('controller_action_predispatch', 1);
        $this->assertEventDispatchCount('controller_action_predispatch_cms', 1);
        $this->assertEventDispatchCount('controller_action_predispatch_cms_index_index', 1);
        $this->assertEventDispatchCount('controller_action_postdispatch_cms_index_index', 0);
        $this->assertEventDispatchCount('controller_action_postdispatch_cms', 0);
        $this->assertEventDispatchCount('controller_action_postdispatch', 0);
    }
}
