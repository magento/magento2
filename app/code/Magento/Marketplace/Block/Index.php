<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Marketplace\Block;

/**
 * @api
 * @since 2.0.0
 */
class Index extends \Magento\Backend\Block\Template
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     * @since 2.0.0
     */
    protected $_urlBuilder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->_urlBuilder->getUrl($route, $params);
    }
}
