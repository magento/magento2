<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Helper;

/**
 * Review helper
 *
 * @api
 * @since 100.0.2
 */
class Review extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_REVIEW_ACTIVE = 'catalog/review/active';
    const XML_PATH_DEFAULT_NO_ROUTE_URL = 'web/default/no_route';

    /**
     * Return an indicator of whether or not review is enable
     *
     * @return bool
     */
    public function isEnableReview()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_REVIEW_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get default no route url
     *
     * @return string
     */
    public function getDefaultNoRouteUrl()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_NO_ROUTE_URL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
