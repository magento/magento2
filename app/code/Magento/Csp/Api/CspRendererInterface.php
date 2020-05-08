<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Api;

use Magento\Framework\App\Response\HttpInterface as HttpResponse;

/**
 * Renders configured CSPs
 */
interface CspRendererInterface
{
    /**
     * Render configured CSP for the given HTTP response.
     *
     * @param HttpResponse $response
     * @return void
     */
    public function render(HttpResponse $response): void;
}
