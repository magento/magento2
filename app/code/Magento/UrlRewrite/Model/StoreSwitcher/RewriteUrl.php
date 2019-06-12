<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Model\StoreSwitcher;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreSwitcherInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Handle url rewrites for redirect url
 */
class RewriteUrl implements StoreSwitcherInterface
{
    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RequestFactory
     */
    private $requestFactory;

    /**
     * @param UrlFinderInterface $urlFinder
     * @param \Magento\Framework\HTTP\PhpEnvironment\RequestFactory $requestFactory
     */
    public function __construct(
        UrlFinderInterface $urlFinder,
        \Magento\Framework\HTTP\PhpEnvironment\RequestFactory $requestFactory
    ) {
        $this->urlFinder = $urlFinder;
        $this->requestFactory = $requestFactory;
    }

    /**
     * Switch to another store.
     *
     * @param StoreInterface $fromStore
     * @param StoreInterface $targetStore
     * @param string $redirectUrl
     * @return string
     */
    public function switch(StoreInterface $fromStore, StoreInterface $targetStore, string $redirectUrl): string
    {
        $targetUrl = $redirectUrl;
        /** @var \Magento\Framework\HTTP\PhpEnvironment\Request $request */
        $request = $this->requestFactory->create(['uri' => $targetUrl]);

        $urlPath = ltrim($request->getPathInfo(), '/');

        if ($targetStore->isUseStoreInUrl()) {
            // Remove store code in redirect url for correct rewrite search
            $storeCode = preg_quote($targetStore->getCode() . '/', '/');
            $pattern = "@^($storeCode)@";
            $urlPath = preg_replace($pattern, '', $urlPath);
        }

        $oldStoreId = $fromStore->getId();
        $oldRewrite = $this->urlFinder->findOneByData([
            UrlRewrite::REQUEST_PATH => $urlPath,
            UrlRewrite::STORE_ID => $oldStoreId,
        ]);
        if ($oldRewrite) {
            $targetUrl = $targetStore->getBaseUrl();
            // look for url rewrite match on the target store
            $currentRewrite = $this->urlFinder->findOneByData([
                UrlRewrite::TARGET_PATH => $oldRewrite->getTargetPath(),
                UrlRewrite::STORE_ID => $targetStore->getId(),
            ]);
            if ($currentRewrite) {
                $targetUrl .= $currentRewrite->getRequestPath();
            }
        } else {
            $existingRewrite = $this->urlFinder->findOneByData([
                UrlRewrite::REQUEST_PATH => $urlPath
            ]);
            $currentRewrite = $this->urlFinder->findOneByData([
                UrlRewrite::REQUEST_PATH => $urlPath,
                UrlRewrite::STORE_ID => $targetStore->getId(),
            ]);

            if ($existingRewrite && !$currentRewrite) {
                /** @var \Magento\Framework\App\Response\Http $response */
                $targetUrl = $targetStore->getBaseUrl();
            }
        }
        return $targetUrl;
    }
}
