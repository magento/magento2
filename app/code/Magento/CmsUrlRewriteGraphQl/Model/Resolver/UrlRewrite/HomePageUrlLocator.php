<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsUrlRewriteGraphQl\Model\Resolver\UrlRewrite;

use Magento\Cms\Helper\Page;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\UrlRewriteGraphQl\Model\Resolver\UrlRewrite\CustomUrlLocatorInterface;

class HomePageUrlLocator implements CustomUrlLocatorInterface
{
    public const HOME_PAGE_URL_DELIMITER = '|';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
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

            return strtok($homePageUrl, self::HOME_PAGE_URL_DELIMITER);
        }

        return null;
    }
}
