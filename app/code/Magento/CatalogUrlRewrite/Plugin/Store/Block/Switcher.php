<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Plugin\Store\Block;

use Magento\Framework\Data\Helper\PostHelper;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\Store;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

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
     * @param PostHelper $postHelper
     * @param  UrlFinderInterface $urlFinder
     */
    public function __construct(
        PostHelper $postHelper,
        UrlFinderInterface $urlFinder
    ) {
        $this->postHelper = $postHelper;
        $this->urlFinder = $urlFinder;
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

        $currentRewrite = $this->urlFinder->findOneByData([
            UrlRewrite::REQUEST_PATH => ltrim($urlPath, '/'),
            UrlRewrite::STORE_ID => $store->getId(),
        ]);

        $urlToSwitch = (is_null($currentRewrite)) ? $baseUrl : $currentUrl;
        return $this->postHelper->getPostData($urlToSwitch, $data);
    }
}
