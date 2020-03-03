<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\HTTP\AsyncClient;

use Magento\Framework\Async\CancelableDeferredInterface;

/**
 * Deferred HTTP response.
 */
interface HttpResponseDeferredInterface extends CancelableDeferredInterface
{
    /**
     * @inheritdoc
     * @return Response HTTP response.
     * @throws HttpException When failed to send the request,
     * if response has 400+ status code it will not be treated as an exception.
     */
    public function get(): Response;
}
