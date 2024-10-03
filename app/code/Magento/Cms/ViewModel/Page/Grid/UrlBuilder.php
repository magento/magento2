<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\ViewModel\Page\Grid;

use Magento\Cms\Model\Page\TargetUrlBuilderInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Url builder class used to compose dynamic urls.
 */
class UrlBuilder
{
    /**
     * @var UrlInterface
     */
    private $frontendUrlBuilder;

    /**
     * @var EncoderInterface
     */
    private $urlEncoder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TargetUrlBuilderInterface
     */
    private $getTargetUrl;

    /**
     * @param UrlInterface $frontendUrlBuilder
     * @param EncoderInterface $urlEncoder
     * @param StoreManagerInterface $storeManager
     * @param TargetUrlBuilderInterface|null $getTargetUrl
     */
    public function __construct(
        UrlInterface $frontendUrlBuilder,
        EncoderInterface $urlEncoder,
        StoreManagerInterface $storeManager,
        ?TargetUrlBuilderInterface $getTargetUrl = null
    ) {
        $this->frontendUrlBuilder = $frontendUrlBuilder;
        $this->urlEncoder = $urlEncoder;
        $this->storeManager = $storeManager;
        $this->getTargetUrl = $getTargetUrl ?:
            ObjectManager::getInstance()->get(TargetUrlBuilderInterface::class);
    }

    /**
     * Get action url
     *
     * @param string $routePath
     * @param string $scope
     * @param string $store
     * @return string
     */
    public function getUrl($routePath, $scope, $store)
    {
        if ($scope) {
            $this->frontendUrlBuilder->setScope($scope);
            $targetUrl = $this->getTargetUrl->process($routePath, $store);
            $href = $this->frontendUrlBuilder->getUrl(
                'stores/store/switch',
                [
                    '_current' => false,
                    '_nosid' => true,
                    '_query' => $this->prepareRequestQuery($store, $targetUrl)
                ]
            );
        } else {
            $href = $this->frontendUrlBuilder->getUrl(
                $routePath,
                [
                    '_current' => false,
                    '_nosid' => true
                ]
            );
        }

        return $href;
    }

    /**
     * Prepare request query
     *
     * @param string $store
     * @param string $href
     * @return array
     */
    private function prepareRequestQuery(string $store, string $href) : array
    {
        $storeView = $this->storeManager->getDefaultStoreView();
        $query = [
            StoreManagerInterface::PARAM_NAME => $store,
            ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlEncoder->encode($href)
        ];
        if (null !== $storeView && $storeView->getCode() !== $store) {
            $query['___from_store'] = $storeView->getCode();
        }

        return $query;
    }
}
