<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Action;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;

/**
 * Extend from this class to create actions controllers in frontend area of your application.
 * It contains standard action behavior (event dispatching, flag checks)
 * Action classes that do not extend from this class will lose this behavior and might not function correctly
 *
 * TODO: Remove this class. Allow implementation of Action Controllers by just implementing Action Interface.
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 2.0.0
 */
abstract class Action extends AbstractAction
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * Namespace for session.
     * Should be defined for proper working session.
     *
     * @var string
     * @since 2.0.0
     */
    protected $_sessionNamespace;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\App\ActionFlag
     * @since 2.0.0
     */
    protected $_actionFlag;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     * @since 2.0.0
     */
    protected $_redirect;

    /**
     * @var \Magento\Framework\App\ViewInterface
     * @since 2.0.0
     */
    protected $_view;

    /**
     * @var \Magento\Framework\UrlInterface
     * @since 2.0.0
     */
    protected $_url;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     * @since 2.0.0
     */
    protected $messageManager;

    /**
     * @param Context $context
     * @since 2.0.0
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
        $this->_objectManager = $context->getObjectManager();
        $this->_eventManager = $context->getEventManager();
        $this->_url = $context->getUrl();
        $this->_actionFlag = $context->getActionFlag();
        $this->_redirect = $context->getRedirect();
        $this->_view = $context->getView();
        $this->messageManager = $context->getMessageManager();
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotFoundException
     * @since 2.0.0
     */
    public function dispatch(RequestInterface $request)
    {
        $this->_request = $request;
        $profilerKey = 'CONTROLLER_ACTION:' . $request->getFullActionName();
        $eventParameters = ['controller_action' => $this, 'request' => $request];
        $this->_eventManager->dispatch('controller_action_predispatch', $eventParameters);
        $this->_eventManager->dispatch('controller_action_predispatch_' . $request->getRouteName(), $eventParameters);
        $this->_eventManager->dispatch(
            'controller_action_predispatch_' . $request->getFullActionName(),
            $eventParameters
        );
        \Magento\Framework\Profiler::start($profilerKey);

        $result = null;
        if ($request->isDispatched() && !$this->_actionFlag->get('', self::FLAG_NO_DISPATCH)) {
            \Magento\Framework\Profiler::start('action_body');
            $result = $this->execute();
            \Magento\Framework\Profiler::start('postdispatch');
            if (!$this->_actionFlag->get('', self::FLAG_NO_POST_DISPATCH)) {
                $this->_eventManager->dispatch(
                    'controller_action_postdispatch_' . $request->getFullActionName(),
                    $eventParameters
                );
                $this->_eventManager->dispatch(
                    'controller_action_postdispatch_' . $request->getRouteName(),
                    $eventParameters
                );
                $this->_eventManager->dispatch('controller_action_postdispatch', $eventParameters);
            }
            \Magento\Framework\Profiler::stop('postdispatch');
            \Magento\Framework\Profiler::stop('action_body');
        }
        \Magento\Framework\Profiler::stop($profilerKey);
        return $result ?: $this->_response;
    }

    /**
     * Throw control to different action (control and module if was specified).
     *
     * @param string $action
     * @param string|null $controller
     * @param string|null $module
     * @param array|null $params
     * @return void
     * @since 2.0.0
     */
    protected function _forward($action, $controller = null, $module = null, array $params = null)
    {
        $request = $this->getRequest();

        $request->initForward();

        if (isset($params)) {
            $request->setParams($params);
        }

        if (isset($controller)) {
            $request->setControllerName($controller);

            // Module should only be reset if controller has been specified
            if (isset($module)) {
                $request->setModuleName($module);
            }
        }

        $request->setActionName($action);
        $request->setDispatched(false);
    }

    /**
     * Set redirect into response
     *
     * @param   string $path
     * @param   array $arguments
     * @return  ResponseInterface
     * @since 2.0.0
     */
    protected function _redirect($path, $arguments = [])
    {
        $this->_redirect->redirect($this->getResponse(), $path, $arguments);
        return $this->getResponse();
    }

    /**
     * @return \Magento\Framework\App\ActionFlag
     * @since 2.0.0
     */
    public function getActionFlag()
    {
        return $this->_actionFlag;
    }
}
