<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
/**
 * Frontend form key content block
 */
namespace Magento\Cookie\Block;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @api
 * @since 100.0.2
 *
 * Class \Magento\Cookie\Block\RequireCookie
 */
class RequireCookie extends \Magento\Framework\View\Element\Template
{
    /**
     * Retrieve script options encoded to json
     *
     * @return string
     */
    public function getScriptOptions()
    {
        $isRedirectCmsPage = ObjectManager::getInstance()->get(ScopeConfigInterface::class)
            ->getValue('web/browser_capabilities/cookies');
        $params = [
            'noCookieUrl' => $this->escapeUrl($this->getUrl('cookie/index/noCookies/')),
            'triggers' => $this->escapeHtml($this->getTriggers()),
            'isRedirectCmsPage' => (boolean)$isRedirectCmsPage
        ];
        return json_encode($params);
    }
}
