<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Model\App\Response;

/**
 * HTTP response plugin for frontend.
 */
class HttpPlugin
{
    /** @var \Magento\Framework\Registry */
    protected $registry;

    /**
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(\Magento\Framework\Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Set proper value of X-Magento-Vary cookie.
     *
     * @param \Magento\Framework\App\Response\Http $subject
     * @return void
     */
    public function beforeSendResponse(\Magento\Framework\App\Response\Http $subject)
    {
        if ($this->registry->registry('use_page_cache_plugin')) {
            $subject->sendVary();
        }
    }
}
