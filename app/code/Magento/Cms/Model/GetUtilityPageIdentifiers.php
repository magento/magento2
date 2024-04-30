<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model;

use Magento\Cms\Api\GetUtilityPageIdentifiersInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Utility Cms Pages.
 */
class GetUtilityPageIdentifiers implements GetUtilityPageIdentifiersInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * UtilityCmsPage constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get List Page Identifiers.
     * When two CMS pages have the same URL key (in different stores),
     * the value stored in configuration is 'url-key|ID'.
     * This method strips the trailing '|ID' so that this method returns
     * only configured url-key mathcing the current scope store ID.
     * See https://github.com/magento/magento2/issues/35001
     *
     * @return array
     */
    public function execute()
    {
        $homePageIdentifier = $this->scopeConfig->getValue(
            'web/default/cms_home_page',
            ScopeInterface::SCOPE_STORE
        );
        $noRouteIdentifier  = $this->scopeConfig->getValue(
            'web/default/cms_no_route',
            ScopeInterface::SCOPE_STORE
        );

        $noCookieIdentifier = $this->scopeConfig->getValue(
            'web/default/cms_no_cookies',
            ScopeInterface::SCOPE_STORE
        );

        return [strtok($homePageIdentifier, '|'), strtok($noRouteIdentifier, '|'), strtok($noCookieIdentifier, '|')];
    }
}
