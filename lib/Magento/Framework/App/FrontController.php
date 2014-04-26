<?php
/**
 * Front controller responsible for dispatching application requests
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\App;

class FrontController implements FrontControllerInterface
{
    /**
     * @var RouterList
     */
    protected $_routerList;

    /**
     * @param RouterList $routerList
     */
    public function __construct(RouterList $routerList)
    {
        $this->_routerList = $routerList;
    }

    /**
     * Perform action and generate response
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws \LogicException
     */
    public function dispatch(RequestInterface $request)
    {
        \Magento\Framework\Profiler::start('routers_match');
        $routingCycleCounter = 0;
        $response = null;
        while (!$request->isDispatched() && $routingCycleCounter++ < 100) {
            foreach ($this->_routerList as $router) {
                try {
                    $actionInstance = $router->match($request);
                    if ($actionInstance) {
                        $request->setDispatched(true);
                        $actionInstance->getResponse()->setNoCacheHeaders();
                        $response = $actionInstance->dispatch($request);
                        break;
                    }
                } catch (Action\NotFoundException $e) {
                    $request->initForward();
                    $request->setActionName('noroute');
                    $request->setDispatched(false);
                    break;
                }
            }
        }
        \Magento\Framework\Profiler::stop('routers_match');
        if ($routingCycleCounter > 100) {
            throw new \LogicException('Front controller reached 100 router match iterations');
        }
        return $response;
    }
}
