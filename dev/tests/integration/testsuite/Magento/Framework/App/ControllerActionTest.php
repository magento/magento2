<?php declare(strict_types=1);

namespace Magento\Framework\App;

use Magento\Backend\Model\Auth as BackendAuth;
use Magento\Backend\Model\UrlInterface as BackendUrl;
use Magento\Framework\App\TestStubs\InheritanceBasedBackendAction;
use Magento\Framework\App\TestStubs\InheritanceBasedFrontendAction;
use Magento\Framework\App\TestStubs\InterfaceOnlyBackendAction;
use Magento\Framework\App\TestStubs\InterfaceOnlyFrontendAction;
use Magento\Framework\Event;
use Magento\Security\Model\Plugin\Auth as SecurityAuth;
use Magento\TestFramework\Bootstrap as TestFramework;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ControllerActionTest extends TestCase
{
    public function setupEventManagerSpy(): void
    {
        /** @var ObjectManager $objectManager */
        $objectManager = ObjectManager::getInstance();

        $originalEventManager = $objectManager->create(Event\ManagerInterface::class);
        $eventManagerSpy = new class($originalEventManager) implements Event\ManagerInterface
        {
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

        $objectManager->addSharedInstance($eventManagerSpy, Event\Manager\Proxy::class);
    }

    private function assertEventDispatchCount($eventName, $expectedCount): void
    {
        $message = sprintf('Event %s was expected to be dispatched %d time(s).', $eventName, $expectedCount);
        $this->assertCount($expectedCount, $this->getEventManager()->spyOnDispatchedEvent($eventName), $message);
    }

    /**
     * @return \Magento\Framework\App\Request\Http
     */
    private function getRequest(): RequestInterface
    {
        return ObjectManager::getInstance()->get(\Magento\Framework\App\Request\Http::class);
    }

    private function fakeBackendAuthentication()
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
     * @magentoAppIsolation enabled
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
     * @magentoAppIsolation enabled
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
     * @magentoAppIsolation enabled
     */
    public function testInheritanceBasedAdminhtmlActionDispatchesEvents()
    {
        $this->fakeBackendAuthentication();
        
        $this->setupEventManagerSpy();

        /** @var InheritanceBasedBackendAction $action */
        $action = ObjectManager::getInstance()->create(InheritanceBasedBackendAction::class);
        $this->configureRequestForAction('testroute', 'actionpath', 'actionname');

        $action->dispatch($this->getRequest());

        $this->assertPreAndPostDispatchEventsAreDispatched();
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
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
}
