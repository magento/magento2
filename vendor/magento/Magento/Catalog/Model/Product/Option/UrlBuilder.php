<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model\Product\Option;

class UrlBuilder
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_frontendUrlBuilder;

    /**
     * @param \Magento\Framework\UrlInterface $frontendUrlBuilder
     */
    public function __construct(\Magento\Framework\UrlInterface $frontendUrlBuilder)
    {
        $this->_frontendUrlBuilder = $frontendUrlBuilder;
    }

    /**
     * @param string|null $route
     * @param array|null $params
     * @return string
     */
    public function getUrl($route, $params)
    {
        return $this->_frontendUrlBuilder->getUrl($route, $params);
    }
}
