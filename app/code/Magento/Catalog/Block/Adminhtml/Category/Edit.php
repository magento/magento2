<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Category;

/**
 * @api
 */
class Edit extends \Magento\Framework\View\Element\Template
{
    /**
     * Return URL for refresh input element 'path' in form
     *
     * @return string
     * @since 2.1.0
     */
    public function getRefreshPathUrl()
    {
        return $this->getUrl('catalog/*/refreshPath', ['_current' => true]);
    }
}
