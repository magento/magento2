<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Marketplace\Block;

/**
 * @api
 * @since 100.0.2
 */
class Index extends \Magento\Backend\Block\Template
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context)
    {
        $this->_urlBuilder = $context->getUrlBuilder();
        parent::__construct($context);
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->_urlBuilder->getUrl($route, $params);
    }
}
