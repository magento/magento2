<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Model\App\Response;

/**
 * HTTP response plugin for frontend.
 */
class HttpPlugin
{
    /**
     * Set proper value of X-Magento-Vary cookie.
     *
     * @param \Magento\Framework\App\Response\Http $subject
     * @return void
     */
    public function beforeSendResponse(\Magento\Framework\App\Response\Http $subject)
    {
        if ($subject instanceof \Magento\Framework\App\PageCache\NotCacheableInterface) {
            return;
        }
        $subject->sendVary();
    }
}
