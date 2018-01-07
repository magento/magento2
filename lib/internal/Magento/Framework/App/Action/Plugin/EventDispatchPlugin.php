<?php declare(strict_types=1);

namespace Magento\Framework\App\Action\Plugin;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\HTTP\PhpEnvironment\Response;

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

    public function __construct(RequestInterface $request, ManagerInterface $eventManager, ActionFlag $actionFlag)
    {
        $this->request = $request;
        $this->eventManager = $eventManager;
        $this->actionFlag = $actionFlag;
    }

    public function beforeExecute(ActionInterface $subject)
    {
        $this->dispatchPreDispatchEvents($subject);
        
        return [];
    }

    /**
     * @param ActionInterface $subject
     * @return mixed[]
     */
    private function getEventParameters(ActionInterface $subject): array
    {
        return ['controller_action' => $subject, 'request' => $this->request];
    }

    /**
     * @param ActionInterface $subject
     * @param ResultInterface|Response|null $result
     * @return ResultInterface|Response|null
     */
    public function afterExecute(ActionInterface $subject, $result)
    {
        if (! $this->isSetActionNoPostDispatchFlag()) {
            $this->dispatchPostDispatchEvents($subject);
        }
        
        return $result;
    }

    /**
     * @param ActionInterface $subject
     * @return bool
     * 
     */
    private function isSetActionNoPostDispatchFlag(): bool
    {
        return $this->actionFlag->get('', Action::FLAG_NO_DISPATCH) ||
               $this->actionFlag->get('', Action::FLAG_NO_POST_DISPATCH);
    }

    /**
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
     * @param ActionInterface $action
     */
    private function dispatchPostDispatchEvents(ActionInterface $action)
    {
        $this->eventManager->dispatch(
            'controller_action_postdispatch_' . $this->request->getFullActionName(),
            $this->getEventParameters($action)
        );
        $this->eventManager->dispatch(
            'controller_action_postdispatch_' . $this->request->getRouteName(),
            $this->getEventParameters($action)
        );
        $this->eventManager->dispatch('controller_action_postdispatch', $this->getEventParameters($action));
    }
}
