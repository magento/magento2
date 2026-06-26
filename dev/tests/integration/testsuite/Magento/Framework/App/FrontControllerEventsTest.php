<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App;

use Magento\Cms\Test\Fixture\Page as PageFixture;
use Magento\Framework\Event\ManagerInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * @magentoAppIsolation enabled
 */
class FrontControllerEventsTest extends AbstractController
{
    /**
     * @var ManagerInterface
     */
    private $eventManagerSpy;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->eventManagerSpy = $this->setupEventManagerSpy();
    }

    /**
     * Test if frontend controller dispatches events for a regular action
     *
     * @return void
     */
    public function testFrontendControllerDispatchesEvents(): void
    {
        $this->dispatch('/cms/index/index');

        $this->assertPreAndPostDispatchEventsAreDispatched();
    }

    /**
     * Test if frontend controller dispatches events once for forwarded CMS pages
     *
     * A CMS page request is forwarded by \Magento\Cms\Controller\Router to "cms/page/view".
     * Pre/post-dispatch events must be triggered only for the resulting action and not for the forward itself.
     *
     * @return void
     */
    #[DataFixture(PageFixture::class, ['identifier' => 'front-controller-forward', 'store_id' => 0], 'page')]
    public function testFrontendControllerDispatchesEventsOnceForForwardedCmsPage(): void
    {
        $this->dispatch('/front-controller-forward');

        $this->assertEventDispatchCount('controller_action_predispatch', 1);
        $this->assertEventDispatchCount('controller_action_postdispatch', 1);
    }

    /**
     * Test if no dispatch flag prevents execution and post-dispatch events
     *
     * @return void
     */
    public function testSettingTheNoDispatchActionFlagProhibitsExecuteAndPostdispatchEvents(): void
    {
        /** @var ActionFlag $actionFlag */
        $actionFlag = $this->_objectManager->get(ActionFlag::class);
        $actionFlag->set('', ActionInterface::FLAG_NO_DISPATCH, true);

        $this->dispatch('/cms/index/index');

        $this->assertPreDispatchEventsAreDispatched();
    }

    /**
     * Register a spy that records every dispatched event while delegating to the real event manager
     *
     * @return ManagerInterface
     */
    private function setupEventManagerSpy(): ManagerInterface
    {
        $eventManager = $this->_objectManager->get(ManagerInterface::class);
        $eventManagerSpy = new class($eventManager) implements ManagerInterface {
            /**
             * @var array[]
             */
            private $dispatchedEvents = [];

            /**
             * @param ManagerInterface $delegate
             */
            public function __construct(private ManagerInterface $delegate)
            {
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

        $this->_objectManager->addSharedInstance($eventManagerSpy, get_class($eventManager));

        return $eventManagerSpy;
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
        $this->assertCount($expectedCount, $this->eventManagerSpy->spyOnDispatchedEvent($eventName), $message);
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
