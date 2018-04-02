<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Plugin\Store\Block;

use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\App\Route\ConfigInterface as RouteConfig;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\Store;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Plugin makes connection between Store and UrlRewrite modules
 * because Magento\Store\Block\Switcher should not know about UrlRewrite module functionality
 */
class Switcher
{
    /**
     * @var PostHelper
     */
    private $postHelper;

    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var RouteConfig
     */
    private $routeConfig;

    /**
     * @param PostHelper $postHelper
     * @param UrlFinderInterface $urlFinder
     * @param RouteConfig $routeConfig
     */
    public function __construct(
        PostHelper $postHelper,
        UrlFinderInterface $urlFinder,
        RouteConfig $routeConfig
    ) {
        $this->postHelper = $postHelper;
        $this->urlFinder = $urlFinder;
        $this->routeConfig = $routeConfig;
    }

    /**
     * @param \Magento\Store\Block\Switcher $subject
     * @param string $result
     * @param Store $store
     * @param array $data
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetTargetStorePostData(
        \Magento\Store\Block\Switcher $subject,
        string $result,
        Store $store,
        array $data = []
    ): string {
        $data[StoreResolverInterface::PARAM_NAME] = $store->getCode();

        $currentUrl = $store->getCurrentUrl(true);
        $baseUrl = $store->getBaseUrl();
        $urlPath = parse_url($currentUrl, PHP_URL_PATH);

        $urlToSwitch = $currentUrl;

        //check rewrites for non-existing routes
        $frontName = ltrim($urlPath, '/');
        if (false === $this->routeConfig->getRouteByFrontName($frontName)) {
            $currentRewrite = $this->urlFinder->findOneByData([
                UrlRewrite::REQUEST_PATH => $frontName,
                UrlRewrite::STORE_ID => $store->getId(),
            ]);
            if (null === $currentRewrite) {
                $urlToSwitch = $baseUrl;
            }
        }

        return $this->postHelper->getPostData($urlToSwitch, $data);
    }
}
