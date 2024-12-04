<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Model\App\Response;

use Magento\Framework\App\PageCache\NotCacheableInterface;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Http\Context;

/**
 * HTTP response plugin for frontend.
 */
class HttpPlugin
{
    /**
     * @param Context $context
     * @param HttpRequest $request
     */
    public function __construct(
        private Context $context,
        private HttpRequest $request
    ) {
    }

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

        $currentVary = $this->context->getVaryString();
        $varyCookie = $this->request->get(HttpResponse::COOKIE_VARY_STRING);
        if ($currentVary !== $varyCookie) {
            //prevent caching with the old vary cookie
            $subject->setNoCacheHeaders();
        }
        $subject->sendVary();
    }
}
