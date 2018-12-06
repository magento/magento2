<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swagger\Block;

use Magento\Framework\View\Element\Template;

/**
 * Class Index
 *
 * @api
 * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
 */
class Index extends Template
{
    /**
     * @return mixed|string
     */
    private function getParamStore()
    {
        return $this->stripTags($this->getRequest()->getParam('store')) ?: 'all';
    }

    /**
     * @return string
     */
    public function getSchemaUrl()
    {
        return rtrim($this->getBaseUrl(), '/') . '/rest/' . $this->getParamStore() . '/schema?services=all';
    }
}
