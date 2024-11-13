<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Controller\Rest;

use Magento\Framework\App\Backpressure\BackpressureExceededException;
use Magento\Framework\App\BackpressureEnforcerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Webapi\Authorization;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Webapi\Backpressure\BackpressureContextFactory;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Webapi\Controller\Rest\Router\Route;

/**
 * Validates a request
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

    /**
     * @var BackpressureContextFactory
     */
    private BackpressureContextFactory $backpressureContextFactory;

    /**
     * @var BackpressureEnforcerInterface
     */
    private BackpressureEnforcerInterface $backpressureEnforcer;

    /**
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
     * Validates the request
     *
     * @throws AuthorizationException
     * @throws WebapiException
     * @return void
     */
    public function validate()
    {
        $route = $this->router->match($this->request);
        $this->checkPermissions($route);
        $this->onlyHttps($route);
        $this->checkBackpressure($route);
    }

    /**
     * Perform authentication and authorization
     *
     * @param Route $route
     * @return void
     * @throws AuthorizationException
     */
    private function checkPermissions(Route $route)
    {
        if ($this->authorization->isAllowed($route->getAclResources())) {
            return;
        }

        throw new AuthorizationException(
            __(
                "The consumer isn't authorized to access %resources.",
                ['resources' => implode(', ', $route->getAclResources())]
            )
        );
    }

    /**
     * Checks if operation allowed only in HTTPS
     *
     * @param Route $route
     * @throws WebapiException
     */
    private function onlyHttps(Route $route)
    {
        if ($route->isSecure() && !$this->request->isSecure()) {
            throw new WebapiException(__('Operation allowed only in HTTPS'));
        }
    }

    /**
     * Checks backpressure
     *
     * @param Route $route
     * @throws WebapiException
     */
    private function checkBackpressure(Route $route)
    {
        $context = $this->backpressureContextFactory->create(
            $route->getServiceClass(),
            $route->getServiceMethod(),
            $route->getRoutePath()
        );
        if ($context) {
            try {
                $this->backpressureEnforcer->enforce($context);
            } catch (BackpressureExceededException $exception) {
                throw new WebapiException(
                    __('Too Many Requests'),
                    0,
                    WebapiException::HTTP_TOO_MANY_REQUESTS
                );
            }
        }
    }
}
