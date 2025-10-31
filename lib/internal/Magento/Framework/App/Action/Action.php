<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Action;

use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Profiler;
use Magento\Framework\UrlInterface;

/**
 * Extend from this class to create actions controllers in frontend area of your application.
 * It contains standard action behavior (event dispatching, flag checks)
 * Action classes that do not extend from this class will lose this behavior and might not function correctly
 *
 * @deprecated 103.0.0 Inheritance in controllers should be avoided in favor of composition
 * @see \Magento\Framework\App\ActionInterface
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 100.0.2
 */
abstract class Action extends AbstractAction
{
    /**
     * @var ObjectManagerInterface
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
     * @var EventManagerInterface
     */
    protected $_eventManager;

    /**
     * @var ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var RedirectInterface
     */
    protected $_redirect;

    /**
     * @var ViewInterface
     */
    protected $_view;

    /**
     * @var UrlInterface
     */
    protected $_url;

    /**
     * @var MessageManagerInterface
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
        Profiler::start($profilerKey);

        $result = null;
        if ($request->isDispatched() && !$this->_actionFlag->get('', self::FLAG_NO_DISPATCH)) {
            Profiler::start('action_body');
            $result = $this->execute();
            Profiler::stop('action_body');
        }
        Profiler::stop($profilerKey);
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
    protected function _forward($action, $controller = null, $module = null, ?array $params = null)
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
     * @param string $path
     * @param array $arguments
     * @return ResponseInterface
     */
    protected function _redirect($path, $arguments = [])
    {
        $this->_redirect->redirect($this->getResponse(), $path, $arguments);
        return $this->getResponse();
    }

    /**
     * Returns ActionFlag value
     *
     * @return \Magento\Framework\App\ActionFlag
     */
    public function getActionFlag()
    {
        return $this->_actionFlag;
    }
}
