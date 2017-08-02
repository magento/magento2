<?php
/**
 * Redirect action class
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Action;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

/**
 * Class \Magento\Framework\App\Action\Redirect
 *
 * @since 2.0.0
 */
class Redirect extends AbstractAction
{
    /**
     * Redirect response
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function dispatch(RequestInterface $request)
    {
        return $this->execute();
    }

    /**
     * @return ResponseInterface
     * @since 2.0.0
     */
    public function execute()
    {
        return $this->_response;
    }
}
