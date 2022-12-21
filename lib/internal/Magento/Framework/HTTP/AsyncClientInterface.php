<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\HTTP;

use Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface;
use Magento\Framework\HTTP\AsyncClient\Request;

/**
 * Asynchronous HTTP client.
 *
 * @api
 */
interface AsyncClientInterface
{
    /**
     * Perform an HTTP request.
     *
     * @param Request $request
     * @return HttpResponseDeferredInterface
     */
    public function request(Request $request): HttpResponseDeferredInterface;
}
