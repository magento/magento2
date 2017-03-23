<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Action;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;

/**
 * Default implementation of application action controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class Action extends AbstractAction
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Namespace for session.
     * Should be defined for proper working session.
     *
     * @var string
     */
    protected $_sessionNamespace;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $_redirect;

    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $_view;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param Context $context
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
     */
    protected function _redirect($path, $arguments = [])
    {
        $this->_redirect->redirect($this->getResponse(), $path, $arguments);
        return $this->getResponse();
    }

    /**
     * @return \Magento\Framework\App\ActionFlag
     */
    public function getActionFlag()
    {
        return $this->_actionFlag;
    }
}
