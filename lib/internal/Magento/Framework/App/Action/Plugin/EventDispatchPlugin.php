<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Action\Plugin;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\HTTP\PhpEnvironment\Response;
use Magento\Framework\Profiler;

/**
 * Dispatch the controller_action_predispatch and controller_action_post_dispatch events.
 */
class EventDispatchPlugin
{
    /**
     * @var Http|RequestInterface
     */
    private $request;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @param RequestInterface $request
     * @param ManagerInterface $eventManager
     * @param ActionFlag $actionFlag
     */
    public function __construct(RequestInterface $request, ManagerInterface $eventManager, ActionFlag $actionFlag)
    {
        $this->request = $request;
        $this->eventManager = $eventManager;
        $this->actionFlag = $actionFlag;
    }

    /**
     * Trigger the controller_action_predispatch events
     *
     * @param ActionInterface $subject
     */
    public function beforeExecute(ActionInterface $subject)
    {
        $this->dispatchPreDispatchEvents($subject);
    }

    /**
     * Build the event parameter array
     *
     * @param ActionInterface $subject
     * @return array
     */
    private function getEventParameters(ActionInterface $subject): array
    {
        return ['controller_action' => $subject, 'request' => $this->request];
    }

    /**
     * Trigger the controller_action_postdispatch events if the suppressing action flag is not set
     *
     * @param ActionInterface $subject
     * @param ResultInterface|Response|null $result
     * @return ResultInterface|Response|null
     */
    public function afterExecute(ActionInterface $subject, $result)
    {
        if (!$this->isSetActionNoPostDispatchFlag()) {
            $this->dispatchPostDispatchEvents($subject);
        }

        return $result;
    }

    /**
     * Check if action flags are set that would suppress the post dispatch events.
     *
     * @return bool
     */
    private function isSetActionNoPostDispatchFlag(): bool
    {
        return $this->actionFlag->get('', Action::FLAG_NO_DISPATCH) ||
            $this->actionFlag->get('', Action::FLAG_NO_POST_DISPATCH);
    }

    /**
     * Dispatch the controller_action_predispatch events.
     *
     * @param ActionInterface $action
     */
    private function dispatchPreDispatchEvents(ActionInterface $action)
    {
        $this->eventManager->dispatch('controller_action_predispatch', $this->getEventParameters($action));
        $this->eventManager->dispatch(
            'controller_action_predispatch_' . $this->request->getRouteName(),
            $this->getEventParameters($action)
        );
        $this->eventManager->dispatch(
            'controller_action_predispatch_' . $this->request->getFullActionName(),
            $this->getEventParameters($action)
        );
    }

    /**
     * Dispatch the controller_action_postdispatch events.
     *
     * @param ActionInterface $action
     */
    private function dispatchPostDispatchEvents(ActionInterface $action)
    {
        Profiler::start('postdispatch');
        $this->eventManager->dispatch(
            'controller_action_postdispatch_' . $this->request->getFullActionName(),
            $this->getEventParameters($action)
        );
        $this->eventManager->dispatch(
            'controller_action_postdispatch_' . $this->request->getRouteName(),
            $this->getEventParameters($action)
        );
        $this->eventManager->dispatch('controller_action_postdispatch', $this->getEventParameters($action));
        Profiler::stop('postdispatch');
    }
}
