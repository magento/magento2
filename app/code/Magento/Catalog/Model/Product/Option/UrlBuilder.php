<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Option;

/**
 * Class \Magento\Catalog\Model\Product\Option\UrlBuilder
 *
 * @since 2.0.0
 */
class UrlBuilder
{
    /**
     * @var \Magento\Framework\UrlInterface
     * @since 2.0.0
     */
    protected $_frontendUrlBuilder;

    /**
     * @param \Magento\Framework\UrlInterface $frontendUrlBuilder
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\UrlInterface $frontendUrlBuilder)
    {
        $this->_frontendUrlBuilder = $frontendUrlBuilder;
    }

    /**
     * @param string|null $route
     * @param array|null $params
     * @return string
     * @since 2.0.0
     */
    public function getUrl($route, $params)
    {
        return $this->_frontendUrlBuilder->getUrl($route, $params);
    }
}
