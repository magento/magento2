<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewriteGraphQl\Model\Resolver;

use Magento\GraphQl\Model\ResolverContextInterface;
use Magento\GraphQl\Model\ResolverInterface;
use Magento\GraphQl\Model\ContextInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * UrlRewrite field resolver, used for GraphQL request processing.
 */
class UrlRewrite implements ResolverInterface
{
    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param UrlFinderInterface $urlFinder
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        UrlFinderInterface $urlFinder,
        StoreManagerInterface $storeManager
    ) {
        $this->urlFinder = $urlFinder;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(array $args, ResolverContextInterface $context)
    {
        if (isset($args['url'])) {
            $urlRewrite = $this->findCanonicalUrl($args['url']->getValue());
            if ($urlRewrite) {
                return [
                    'id' => $urlRewrite->getEntityId(),
                    'canonical_url' => $urlRewrite->getTargetPath(),
                    'type' => $this->sanitizeType($urlRewrite->getEntityType())
                ];
            }
        }
        return null;
    }

    /**
     * Find the canonical url passing through all redirects if any
     *
     * @param string $requestPath
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite|null
     */
    private function findCanonicalUrl(string $requestPath)
    {
        $urlRewrite = $this->findUrlFromRequestPath($requestPath);
        if ($urlRewrite && $urlRewrite->getRedirectType() > 0) {
            while ($urlRewrite && $urlRewrite->getRedirectType() > 0) {
                $urlRewrite = $this->findUrlFromRequestPath($urlRewrite->getTargetPath());
            }
        }
        if (!$urlRewrite) {
            $urlRewrite = $this->findUrlFromTargetPath($requestPath);
        }
        return $urlRewrite;
    }

    /**
     * Find a url from a request url on the current store
     *
     * @param string $requestPath
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite|null
     */
    private function findUrlFromRequestPath(string $requestPath)
    {
        return $this->urlFinder->findOneByData(
            [
                'request_path' => $requestPath,
                'store_id' => $this->storeManager->getStore()->getId()
            ]
        );
    }

    /**
     * Find a url from a target url on the current store
     *
     * @param string $targetPath
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite|null
     */
    private function findUrlFromTargetPath(string $targetPath)
    {
        return $this->urlFinder->findOneByData(
            [
                'target_path' => $targetPath,
                'store_id' => $this->storeManager->getStore()->getId()
            ]
        );
    }

    /**
     * Sanitize the type to fit schema specifications
     *
     * @param string $type
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite|null
     */
    private function sanitizeType(string $type)
    {
        return strtoupper(str_replace('-', '_', $type));
    }
}
