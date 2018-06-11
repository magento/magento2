<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Plugin\Store;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreSwitcherInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Plugin handles url rewrites for redirect url
 */
class RewriteUrlAfterStoreSwitching
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
     * @param StoreSwitcherInterface $subject
     * @param string $result
     * @param StoreInterface $fromStore
     * @param StoreInterface $targetStore
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSwitch(
        StoreSwitcherInterface $subject,
        string $result,
        StoreInterface $fromStore,
        StoreInterface $targetStore
    ): string {
        $targetUrl = $result;
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
            // look for url rewrite match on the target store
            $currentRewrite = $this->urlFinder->findOneByData([
                UrlRewrite::REQUEST_PATH => $urlPath,
                UrlRewrite::STORE_ID => $targetStore->getId(),
            ]);
            if (null === $currentRewrite) {
                /** @var \Magento\Framework\App\Response\Http $response */
                $targetUrl = $targetStore->getBaseUrl();
            }
        }

        return $targetUrl;
    }
}
