<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsUrlRewrite\Model\Page;

use Magento\Cms\Model\Page;
use Magento\Cms\Model\Page\TargetUrlBuilderInterface;
use Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Get target Url from routePath and store code.
 */
class TargetUrlBuilder implements TargetUrlBuilderInterface
{
    /**
     * @var UrlInterface
     */
    private $frontendUrlBuilder;

    /**
     * @var Page
     */
    private $cmsPage;

    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var CmsPageUrlPathGenerator
     */
    private $cmsPageUrlPathGenerator;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Initialize constructor
     *
     * @param UrlInterface $frontendUrlBuilder
     * @param StoreManagerInterface $storeManager
     * @param Page $cmsPage
     * @param UrlFinderInterface $urlFinder
     * @param CmsPageUrlPathGenerator $cmsPageUrlPathGenerator
     */
    public function __construct(
        UrlInterface $frontendUrlBuilder,
        StoreManagerInterface $storeManager,
        Page $cmsPage,
        UrlFinderInterface $urlFinder,
        CmsPageUrlPathGenerator $cmsPageUrlPathGenerator
    ) {
        $this->frontendUrlBuilder = $frontendUrlBuilder;
        $this->storeManager = $storeManager;
        $this->cmsPage = $cmsPage;
        $this->urlFinder = $urlFinder;
        $this->cmsPageUrlPathGenerator = $cmsPageUrlPathGenerator;
    }

    /**
     * Get target URL
     *
     * @param string $routePath
     * @param string $store
     * @return string
     * @throws NoSuchEntityException
     */
    public function process(string $routePath, string $store): string
    {
        $storeId = $this->storeManager->getStore($store)->getId();
        $pageId = $this->cmsPage->checkIdentifier($routePath, $storeId);
        $currentUrlRewrite = $this->urlFinder->findOneByData(
            [
                UrlRewrite::REQUEST_PATH => $routePath,
                UrlRewrite::STORE_ID => $storeId,
            ]
        );
        $existingUrlRewrite = $this->urlFinder->findOneByData(
            [
                UrlRewrite::REQUEST_PATH => $routePath
            ]
        );
        if ($currentUrlRewrite === null && $existingUrlRewrite !== null && !empty($pageId)) {
            $cmsPage = $this->cmsPage->load($pageId);
            $routePath = $this->cmsPageUrlPathGenerator->getCanonicalUrlPath($cmsPage);
        }
        return $this->frontendUrlBuilder->getUrl(
            $routePath,
            [
                '_current' => false,
                '_nosid' => true,
                '_query' => [
                    StoreManagerInterface::PARAM_NAME => $store
                ]
            ]
        );
    }
}
