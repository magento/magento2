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
 * @since 2.0.0
 */
class RequireCookie extends \Magento\Framework\View\Element\Template
{
    /**
     * Retrieve script options encoded to json
     *
     * @return string
     * @since 2.0.0
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
