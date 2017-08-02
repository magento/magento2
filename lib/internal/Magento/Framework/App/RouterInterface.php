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
 * @since 2.0.0
 */
interface RouterInterface
{
    /**
     * Match application action by request
     *
     * @param RequestInterface $request
     * @return ActionInterface
     * @since 2.0.0
     */
    public function match(RequestInterface $request);
}
