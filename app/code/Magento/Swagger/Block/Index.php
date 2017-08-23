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
 * @package Magento\Swagger\Block
 */
class Index extends Template
{
    /**
     * @return mixed|string
     */
    public function getParamStore()
    {
        return ($this->getRequest()->getParam('store')) ? $this->getRequest()->getParam('store') : 'all';
    }

    /**
     * @return string
     */
    public function getSchemaUrl()
    {
        return rtrim($this->getBaseUrl(), '/') . '/rest/' . $this->getParamStore() . '/schema?services=all';
    }
}
