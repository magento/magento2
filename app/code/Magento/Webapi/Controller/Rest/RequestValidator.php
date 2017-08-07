<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Controller\Rest;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Webapi\Authorization;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Store\Model\StoreManagerInterface;

/**
 * This class is responsible for validating the request
 * @since 2.1.0
 */
class RequestValidator
{
    /**
     * @var RestRequest
     * @since 2.1.0
     */
    private $request;

    /**
     * @var Router
     * @since 2.1.0
     */
    private $router;

    /**
     * @var StoreManagerInterface
     * @since 2.1.0
     */
    private $storeManager;

    /**
     * @var Authorization
     * @since 2.1.0
     */
    private $authorization;

    /**
     * Initialize dependencies
     *
     * @param RestRequest $request
     * @param Router $router
     * @param StoreManagerInterface $storeManager
     * @param Authorization $authorization
     * @since 2.1.0
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
     * @since 2.1.0
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
     * @since 2.1.0
     */
    private function checkPermissions()
    {
        $route = $this->router->match($this->request);
        if (!$this->authorization->isAllowed($route->getAclResources())) {
            $params = ['resources' => implode(', ', $route->getAclResources())];
            throw new AuthorizationException(
                __('Consumer is not authorized to access %resources', $params)
            );
        }
    }
}
