<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\App\Request\Http as RequestHttp;

/**
 * Interface ExceptionHandler
 */
interface ExceptionHandlerInterface
{
    /**
     * Handles exception of HTTP web application
     *
     * @param Bootstrap $bootstrap
     * @param \Exception $exception
     * @param ResponseHttp $response
     * @param RequestHttp $request
     * @return bool
     */
    public function handle(
        Bootstrap $bootstrap,
        \Exception $exception,
        ResponseHttp $response,
        RequestHttp $request
    ): bool;
}
