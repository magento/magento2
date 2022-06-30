<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\PageCache\Plugin;

use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\Response\HttpInterface;

class AppendNoStoreCacheHeader
{
    /**
     * Set cache-control header
     *
     * @param FrontControllerInterface $controller
     * @param HttpInterface $response
     * @return HttpInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDispatch(FrontControllerInterface $controller, HttpInterface $response): HttpInterface
    {
        $response->setHeader('Cache-Control', 'no-store');
        return $response;
    }
}
