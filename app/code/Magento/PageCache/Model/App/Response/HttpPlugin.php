<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\PageCache\Model\App\Response;

use Laminas\Http\Header\CacheControl;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\PageCache\NotCacheableInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Stdlib\DateTime;

/**
 * HTTP response plugin for frontend.
 */
class HttpPlugin
{
    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * @param Context $context
     * @param HttpRequest $request
     * @param DateTime|null $dateTime
     */
    public function __construct(
        private Context $context,
        private HttpRequest $request,
        ?DateTime $dateTime = null
    ) {
        $this->dateTime = $dateTime ?? ObjectManager::getInstance()->get(DateTime::class);
    }

    /**
     * Set proper value of X-Magento-Vary cookie and keep public responses out of the browser cache.
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
        if (isset($varyCookie) && ($currentVary !== $varyCookie)) {
            $subject->setNoCacheHeaders();
        }
        $this->preventBrowserCaching($subject);
        $subject->sendVary();
    }

    /**
     * Keep a publicly cacheable response cacheable for the shared cache only.
     *
     * Cacheable pages are sent with "public, max-age=<ttl>, s-maxage=<ttl>" (see
     * \Magento\PageCache\Model\Layout\LayoutPlugin). The built-in cache replaces that with no-cache headers
     * before the response is sent (see \Magento\Framework\App\PageCache\Kernel::process) and the shipped
     * Varnish VCL does the same in vcl_deliver, but nothing on the application side did it for the Varnish
     * cache type. A browser that reaches the application without that VCL in front, or through a proxy that
     * passes Cache-Control through, would otherwise keep personalised HTML for the whole TTL, for example the
     * header rendered for a logged-in customer after the customer has logged out. Force max-age=0 for the
     * browser and leave s-maxage untouched so Varnish and CDNs keep their TTL.
     *
     * @param HttpResponse $subject
     * @return void
     */
    private function preventBrowserCaching(HttpResponse $subject): void
    {
        $cacheControl = $subject->getHeader('Cache-Control');
        if (!$cacheControl instanceof CacheControl
            || !$cacheControl->hasDirective('public')
            || !$cacheControl->hasDirective('s-maxage')
        ) {
            return;
        }

        $cacheControl->addDirective('max-age', '0');
        $subject->setHeader('Cache-Control', $cacheControl->getFieldValue(), true);
        $subject->setHeader(
            'Expires',
            $this->dateTime->gmDate(HttpResponse::EXPIRATION_TIMESTAMP_FORMAT, $this->dateTime->strToTime('-1 year')),
            true
        );
    }
}
