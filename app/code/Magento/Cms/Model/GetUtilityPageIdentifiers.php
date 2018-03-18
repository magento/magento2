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
<<<<<<< HEAD
 * Utility Cms Pages.
=======
 * Utility Cms Pages
>>>>>>> upstream/2.2-develop
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
<<<<<<< HEAD
     * Get List Page Identifiers.
     *
=======
     * Get List Page Identifiers
>>>>>>> upstream/2.2-develop
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

        return [$homePageIdentifier, $noRouteIdentifier, $noCookieIdentifier];
    }
}
