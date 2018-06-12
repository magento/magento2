<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsUrlRewriteGraphQl\Model\Resolver\UrlRewrite;

use Magento\UrlRewriteGraphQl\Model\Resolver\UrlRewrite\CustomUrlLocatorInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Cms\Helper\Page;

/**
 * Home page URL locator.
 */
class HomePageUrlLocator implements CustomUrlLocatorInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function locateUrl($urlKey): ?string
    {
        if ($urlKey === '/') {
            $homePageUrl = $this->scopeConfig->getValue(
                Page::XML_PATH_HOME_PAGE,
                ScopeInterface::SCOPE_STORE
            );
            return $homePageUrl;
        }
        return null;
    }
}
