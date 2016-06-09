<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Controller\Rest;

use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Webapi\Controller\Rest\Router;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Magento\Framework\Webapi\Authorization;
use Magento\Framework\Exception\AuthorizationException;

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

    /**
     * Initialize dependencies
     *
     * @param RestRequest $request
     * @param Router $router
     * @param StoreManagerInterface $storeManager
     * @param Authorization $authorization
     */
    public function __construct(
        RestRequest $request,
        Router $router,
        StoreManagerInterface $storeManager,
        Authorization $authorization
    ) {
        $this->request = $request;
        $this->router = $router;
        $this->storeManager = $storeManager;
        $this->authorization = $authorization;
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
                __(AuthorizationException::NOT_AUTHORIZED, $params)
            );
        }
    }
}
