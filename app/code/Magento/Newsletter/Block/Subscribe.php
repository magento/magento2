<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Newsletter subscribe block
 */
namespace Magento\Newsletter\Block;

/**
 * @api
 * @since 100.0.2
 */
class Subscribe extends \Magento\Framework\View\Element\Template
{
    /**
     * Get form action url & set secure param to avoid confirm message when we submit form from secure page to unsecure
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('newsletter/subscriber/new', ['_secure' => true]);
    }
}
