<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Frontend form key content block
 */
namespace Magento\Cookie\Block;

/**
 * @api
 * @since 100.0.2
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
        $params = [
            'noCookieUrl' => $this->escapeUrl($this->getUrl('cookie/index/noCookies/')),
            'triggers' => $this->escapeHtml($this->getTriggers())
        ];
        return json_encode($params);
    }
}
