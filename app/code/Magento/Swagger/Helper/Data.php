<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swagger\Helper;

/**
 * Class Data
 * @package Magento\Swagger\Helper\Data
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
        parent::__construct($context);
    }

    /**
     * @return mixed|string
     */
    public function getParamStore()
    {
        return ($this->request->getParam('store')) ? $this->request->getParam('store') : 'all';
    }

    /**
     * @return string
     */
    public function getSchemaUrl()
    {
        return rtrim($this->getBaseUrl(), '/') . '/rest/' . $this->getParamStore() . '/schema?services=all';
    }
}
