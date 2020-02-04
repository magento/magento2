<?php
/**
 * Router. Matches action from request
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Interface \Magento\Framework\App\RouterInterface
 *
 */
interface RouterInterface
{
    /**
     * Match application action by request.
     *
     * For web requests, return null if the router doesn't match the request
     * but other routers might still match. Or to cause a 404, throw a
     * \Magento\Framework\Exception\NotFoundException.
     * See \Magento\Framework\App\FrontController.
     *
     * For Webapi Rest requests, return FALSE instead of null.
     * See \Magento\Webapi\Controller\Rest\Router.
     *
     * @param RequestInterface $request
     * @return ActionInterface|null|false
     */
    public function match(RequestInterface $request);
}
