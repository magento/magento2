<?php
/**
 * Router. Matches action from request
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Main application route dispatcher
 */
interface RouterInterface
{
    /**
     * Match application action by request
     *
     * Must either return an action capable of handling the request, or null
     *
     * @param RequestInterface $request
     * @return ActionInterface|null
     */
    public function match(RequestInterface $request);
}
