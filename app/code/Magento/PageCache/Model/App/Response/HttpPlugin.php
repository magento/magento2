<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Model\App\Response;

use Magento\Framework\App\PageCache\NotCacheableInterface;
use Magento\Framework\App\Response\Http as HttpResponse;

/**
 * HTTP response plugin for frontend.
 */
class HttpPlugin
{
    /**
     * Set proper value of X-Magento-Vary cookie.
     *
     * @param HttpResponse $subject
     * @return void
     */
    public function beforeSendResponse(HttpResponse $subject)
    {
        if ($subject instanceof NotCacheableInterface
            || $subject->headersSent()
            || $subject->getMetadata("NotCacheable")
        ) {
            return;
        }
        $subject->sendVary();
    }
}
