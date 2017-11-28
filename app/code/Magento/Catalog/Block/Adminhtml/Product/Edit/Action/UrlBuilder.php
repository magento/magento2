<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Action;

use Magento\Store\Api\StoreResolverInterface;

/**
 * Adminhtml catalog product action customer view
 */
class UrlBuilder
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $frontendUrlBuilder;

    /**
     * @param \Magento\Framework\UrlInterface $frontendUrlBuilder
     */
    public function __construct(\Magento\Framework\UrlInterface $frontendUrlBuilder)
    {
        $this->frontendUrlBuilder = $frontendUrlBuilder;
    }

    /**
     * Get action url
     *
     * @param string $routePath
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param string $scope
     * @param string $store
     * @return string
     */
    public function getUrl($routePath, \Magento\Catalog\Api\Data\ProductInterface $product, $scope, $store)
    {
        $this->frontendUrlBuilder->setScope($scope);
        return $this->frontendUrlBuilder->getUrl(
            $routePath,
            [
                'id' => $product->getId(),
                '_current' => false,
                '_nosid' => true,
                '_query' => [StoreResolverInterface::PARAM_NAME => $store]
            ]
        );
    }
}
