<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App;

use Magento\Backend\Model\Auth as BackendAuth;
use Magento\Backend\Model\UrlInterface as BackendUrl;
use Magento\Framework\App\TestStubs\InheritanceBasedBackendAction;
use Magento\Framework\App\TestStubs\InheritanceBasedFrontendAction;
use Magento\Framework\App\TestStubs\InterfaceOnlyBackendAction;
use Magento\Framework\App\TestStubs\InterfaceOnlyFrontendAction;
use Magento\Framework\Event;
use Magento\TestFramework\Bootstrap as TestFramework;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Request as TestHttpRequest;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class ControllerActionTest extends TestCase
{
    public function setupEventManagerSpy()
    {
        /** @var ObjectManager $objectManager */
        $objectManager = ObjectManager::getInstance();

        $originalEventManager = $objectManager->create(Event\ManagerInterface::class);
        $eventManagerSpy = new class($originalEventManager) implements Event\ManagerInterface {
            /**
             * @var Event\ManagerInterface
             */
            private $delegate;

            /**
             * @var array[];
             */
            private $dispatchedEvents = [];

            public function __construct(Event\ManagerInterface $delegate)
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

        $objectManager->addSharedInstance($eventManagerSpy, get_class($originalEventManager));
    }

    private function assertEventDispatchCount($eventName, $expectedCount)
    {
        $message = sprintf('Event %s was expected to be dispatched %d time(s).', $eventName, $expectedCount);
        $this->assertCount($expectedCount, $this->getEventManager()->spyOnDispatchedEvent($eventName), $message);
    }

    /**
     * @return TestHttpRequest
     */
    private function getRequest(): RequestInterface
    {
        return ObjectManager::getInstance()->get(TestHttpRequest::class);
    }

    private function fakeAuthenticatedBackendRequest()
    {
        $objectManager = ObjectManager::getInstance();
        $objectManager->get(BackendUrl::class)->turnOffSecretKey();

        $auth = $objectManager->get(BackendAuth::class);
        $auth->login(TestFramework::ADMIN_NAME, TestFramework::ADMIN_PASSWORD);
    }

    private function configureRequestForAction(string $route, string $actionPath, string $actionName)
    {
        $request = $this->getRequest();

        $request->setRouteName($route);
        $request->setControllerName($actionPath);
        $request->setActionName($actionName);
        $request->setDispatched();
    }

    private function getEventManager(): Event\ManagerInterface
    {
        return ObjectManager::getInstance()->get(Event\ManagerInterface::class);
    }

    private function assertPreAndPostDispatchEventsAreDispatched()
    {
        $this->assertEventDispatchCount('controller_action_predispatch', 1);
        $this->assertEventDispatchCount('controller_action_predispatch_testroute', 1);
        $this->assertEventDispatchCount('controller_action_predispatch_testroute_actionpath_actionname', 1);
        $this->assertEventDispatchCount('controller_action_postdispatch_testroute_actionpath_actionname', 1);
        $this->assertEventDispatchCount('controller_action_postdispatch_testroute', 1);
        $this->assertEventDispatchCount('controller_action_postdispatch', 1);
    }

    /**
     * @magentoAppArea frontend
     */
    public function testInheritanceBasedFrontendActionDispatchesEvents()
    {
        $this->setupEventManagerSpy();

        /** @var InheritanceBasedFrontendAction $action */
        $action = ObjectManager::getInstance()->create(InheritanceBasedFrontendAction::class);
        $this->configureRequestForAction('testroute', 'actionpath', 'actionname');

        $action->dispatch($this->getRequest());

        $this->assertPreAndPostDispatchEventsAreDispatched();
    }

    /**
     * @magentoAppArea frontend
     */
    public function testInterfaceOnlyFrontendActionDispatchesEvents()
    {
        $this->setupEventManagerSpy();

        /** @var InterfaceOnlyFrontendAction $action */
        $action = ObjectManager::getInstance()->create(InterfaceOnlyFrontendAction::class);
        $this->configureRequestForAction('testroute', 'actionpath', 'actionname');

        $action->execute();

        $this->assertPreAndPostDispatchEventsAreDispatched();
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testInheritanceBasedAdminhtmlActionDispatchesEvents()
    {
        $this->fakeAuthenticatedBackendRequest();

        $this->setupEventManagerSpy();

        /** @var InheritanceBasedBackendAction $action */
        $action = ObjectManager::getInstance()->create(InheritanceBasedBackendAction::class);
        $this->configureRequestForAction('testroute', 'actionpath', 'actionname');

        $action->dispatch($this->getRequest());

        $this->assertPreAndPostDispatchEventsAreDispatched();
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testInterfaceOnlyAdminhtmlActionDispatchesEvents()
    {
        $this->setupEventManagerSpy();

        /** @var InterfaceOnlyBackendAction $action */
        $action = ObjectManager::getInstance()->create(InterfaceOnlyBackendAction::class);
        $this->configureRequestForAction('testroute', 'actionpath', 'actionname');

        $action->execute();

        $this->assertPreAndPostDispatchEventsAreDispatched();
    }

    /**
     * @magentoAppArea frontend
     */
    public function testSettingTheNoDispatchActionFlagProhibitsExecuteAndPostdispatchEvents()
    {
        $this->setupEventManagerSpy();

        /** @var InterfaceOnlyFrontendAction $action */
        $action = ObjectManager::getInstance()->create(InterfaceOnlyFrontendAction::class);
        $this->configureRequestForAction('testroute', 'actionpath', 'actionname');

        /** @var ActionFlag $actionFlag */
        $actionFlag = ObjectManager::getInstance()->get(ActionFlag::class);
        $actionFlag->set('', ActionInterface::FLAG_NO_DISPATCH, true);

        $action->execute();

        $this->assertFalse($action->isExecuted(), 'The controller execute() method was not expected to be called.');
        $this->assertEventDispatchCount('controller_action_predispatch', 1);
        $this->assertEventDispatchCount('controller_action_predispatch_testroute', 1);
        $this->assertEventDispatchCount('controller_action_predispatch_testroute_actionpath_actionname', 1);
        $this->assertEventDispatchCount('controller_action_postdispatch_testroute_actionpath_actionname', 0);
        $this->assertEventDispatchCount('controller_action_postdispatch_testroute', 0);
        $this->assertEventDispatchCount('controller_action_postdispatch', 0);
    }
}
