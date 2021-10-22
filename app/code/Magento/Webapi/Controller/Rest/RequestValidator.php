<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Controller\Rest;

use Magento\Framework\App\BackpressureEnforcerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Webapi\Authorization;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Webapi\Backpressure\BackpressureContextFactory;

/**
 * This class is responsible for validating the request
 */
class RequestValidator
{
    /**
     * @var RestRequest
     */
    private $request;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Authorization
     */
    private $authorization;

    private BackpressureContextFactory $backpressureContextFactory;

    private BackpressureEnforcerInterface $backpressureEnforcer;

    /**
     * Initialize dependencies
     *
     * @param RestRequest $request
     * @param Router $router
     * @param StoreManagerInterface $storeManager
     * @param Authorization $authorization
     * @param BackpressureContextFactory|null $backpressureContextFactory
     * @param BackpressureEnforcerInterface|null $backpressureEnforcer
     */
    public function __construct(
        RestRequest $request,
        Router $router,
        StoreManagerInterface $storeManager,
        Authorization $authorization,
        ?BackpressureContextFactory $backpressureContextFactory = null,
        ?BackpressureEnforcerInterface $backpressureEnforcer = null
    ) {
        $this->request = $request;
        $this->router = $router;
        $this->storeManager = $storeManager;
        $this->authorization = $authorization;
        $this->backpressureContextFactory = $backpressureContextFactory
            ?? ObjectManager::getInstance()->get(BackpressureContextFactory::class);
        $this->backpressureEnforcer = $backpressureEnforcer
            ?? ObjectManager::getInstance()->get(BackpressureEnforcerInterface::class);
    }

    /**
     * Validate request
     *
     * @throws AuthorizationException
     * @throws \Magento\Framework\Webapi\Exception
     * @return void
     */
    public function validate()
    {
        $this->checkPermissions();
        $route = $this->router->match($this->request);
        if ($route->isSecure() && !$this->request->isSecure()) {
            throw new \Magento\Framework\Webapi\Exception(__('Operation allowed only in HTTPS'));
        }

        $context = $this->backpressureContextFactory->create(
            $route->getServiceClass(),
            $route->getServiceMethod(),
            $route->getRoutePath()
        );
        if ($context) {
            $this->backpressureEnforcer->enforce($context);
        }
    }

    /**
     * Perform authentication and authorization.
     *
     * @throws \Magento\Framework\Exception\AuthorizationException
     * @return void
     */
    private function checkPermissions()
    {
        $route = $this->router->match($this->request);
        if (!$this->authorization->isAllowed($route->getAclResources())) {
            $params = ['resources' => implode(', ', $route->getAclResources())];
            throw new AuthorizationException(
                __("The consumer isn't authorized to access %resources.", $params)
            );
        }
    }
}
