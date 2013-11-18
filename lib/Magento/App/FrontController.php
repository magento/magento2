<?php
/**
 * Front controller responsible for dispatcing application requests
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\App;

class FrontController implements FrontControllerInterface
{
    /**
     * @var array
     */
    protected $_defaults = array();

    /**
     * @var \Magento\App\RouterInterface[]
     */
    protected $_routerList;

    /**
     * @var \Magento\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\App\ResponseInterface
     */
    protected $_response;

    /**
     * @var ActionInterface
     */
    protected $_action;

    /**
     * @param \Magento\App\ResponseInterface $response
     * @param RouterList $routerList
     * @param array $data
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\App\ResponseInterface $response,
        RouterList $routerList,
        array $data = array()
    ) {
        $this->_routerList = $routerList;
        $this->_response = $response;
    }

    /**
     * Retrieve request object
     *
     * @return \Magento\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Retrieve response object
     *
     * @return \Magento\App\ResponseInterface
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Seta application action
     *
     * @param ActionInterface $action
     */
    public function setAction(ActionInterface $action)
    {
        $this->_action = $action;
    }

    /**
     * @return ActionInterface
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws \LogicException
     */
    public function dispatch(RequestInterface $request)
    {
        $this->_request = $request;
        \Magento\Profiler::start('routers_match');
        $routingCycleCounter = 0;
        while (!$request->isDispatched() && $routingCycleCounter++ < 100) {
            foreach ($this->_routerList as $router) {
                $controllerInstance = $router->match($this->getRequest());
                if ($controllerInstance) {
                    $controllerInstance->dispatch($request->getActionName());
                    break;
                }
            }
        }
        \Magento\Profiler::stop('routers_match');
        if ($routingCycleCounter > 100) {
            throw new \LogicException('Front controller reached 100 router match iterations');
        }
        return $this->getResponse();
    }
}
