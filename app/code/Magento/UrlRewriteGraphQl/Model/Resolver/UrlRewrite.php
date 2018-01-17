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
                    'type' => $urlRewrite->getEntityType()
                ];
            }
        }
        return null;
    }

    /**
     * Recursively find the canonical url passing through all redirects
     *
     * @param string $requestPath
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite|null
     */
    private function findCanonicalUrl(string $requestPath)
    {
        $urlRewrite = $this->findUrl($requestPath);
        if ($urlRewrite->getRedirectType() > 0) {
            while ($urlRewrite && $urlRewrite->getRedirectType() > 0) {
                $urlRewrite = $this->findUrl($urlRewrite->getTargetPath());
            }
        }
        return $urlRewrite;
    }

    /**
     * Find a url from a request url on the current store
     *
     * @param string $requestPath
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite|null
     */
    private function findUrl(string $requestPath)
    {
        return $this->urlFinder->findOneByData(
            [
                'request_path' => $requestPath,
                'store_id' => $this->storeManager->getStore()->getId()
            ]
        );
    }
}
