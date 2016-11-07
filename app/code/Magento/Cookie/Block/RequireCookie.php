<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Frontend form key content block
 */
namespace Magento\Cookie\Block;

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
