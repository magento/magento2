<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Url builder class used to compose dynamic urls.
 */
class UrlBuilder
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $frontendUrlBuilder;

    /**
     * @var EncoderInterface
     */
    private $urlEncoder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Framework\UrlInterface $frontendUrlBuilder
     * @param EncoderInterface|null $urlEncoder
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        \Magento\Framework\UrlInterface $frontendUrlBuilder,
        EncoderInterface $urlEncoder = null,
        StoreManagerInterface $storeManager = null
    ) {
        $this->frontendUrlBuilder = $frontendUrlBuilder;
        $this->urlEncoder = $urlEncoder ?: ObjectManager::getInstance()
            ->get(EncoderInterface::class);
        $this->storeManager = $storeManager?: ObjectManager::getInstance()
            ->get(StoreManagerInterface::class);
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
            $targetUrl = $this->frontendUrlBuilder->getUrl(
                $routePath,
                [
                    '_current' => false,
                    '_nosid' => true,
                    '_query' => [
                        StoreManagerInterface::PARAM_NAME => $store
                    ]
                ]
            );
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
        if ($storeView->getCode() !== $store) {
            $query['___from_store'] = $storeView->getCode();
        }

        return $query;
    }
}
