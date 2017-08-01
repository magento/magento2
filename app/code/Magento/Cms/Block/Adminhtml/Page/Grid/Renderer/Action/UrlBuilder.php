<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action;

use Magento\Store\Api\StoreResolverInterface;

/**
 * Class \Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder
 *
 * @since 2.0.0
 */
class UrlBuilder
{
    /**
     * @var \Magento\Framework\UrlInterface
     * @since 2.0.0
     */
    protected $frontendUrlBuilder;

    /**
     * @param \Magento\Framework\UrlInterface $frontendUrlBuilder
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\UrlInterface $frontendUrlBuilder)
    {
        $this->frontendUrlBuilder = $frontendUrlBuilder;
    }

    /**
     * Get action url
     *
     * @param string $routePath
     * @param string $scope
     * @param string $store
     * @return string
     * @since 2.0.0
     */
    public function getUrl($routePath, $scope, $store)
    {
        $this->frontendUrlBuilder->setScope($scope);
        $href = $this->frontendUrlBuilder->getUrl(
            $routePath,
            [
                '_current' => false,
                '_query' => [StoreResolverInterface::PARAM_NAME => $store]
            ]
        );

        return $href;
    }
}
