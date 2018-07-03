<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewriteGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewriteGraphQl\Model\Resolver\UrlRewrite\CustomUrlLocatorInterface;

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
     * @var ValueFactory
     */
    private $valueFactory;
    
    /**
     * @var CustomUrlLocatorInterface
     */
    private $customUrlLocator;

    /**
     * @param UrlFinderInterface $urlFinder
     * @param StoreManagerInterface $storeManager
     * @param ValueFactory $valueFactory
     * @param CustomUrlLocatorInterface $customUrlLocator
     */
    public function __construct(
        UrlFinderInterface $urlFinder,
        StoreManagerInterface $storeManager,
        ValueFactory $valueFactory,
        CustomUrlLocatorInterface $customUrlLocator
    ) {
        $this->urlFinder = $urlFinder;
        $this->storeManager = $storeManager;
        $this->valueFactory = $valueFactory;
        $this->customUrlLocator = $customUrlLocator;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) : Value {
        $result = function () {
            return null;
        };
        
        if (isset($args['url'])) {
            $url = $args['url'];
            if (substr($url, 0, 1) === '/' && $url !== '/') {
                $url = ltrim($url, '/');
            }
            $customUrl = $this->customUrlLocator->locateUrl($url);
            $url = $customUrl ?: $url;
            $urlRewrite = $this->findCanonicalUrl($url);
            if ($urlRewrite) {
                $urlRewriteReturnArray = [
                    'id' => $urlRewrite->getEntityId(),
                    'canonical_url' => $urlRewrite->getTargetPath(),
                    'type' => $this->sanitizeType($urlRewrite->getEntityType())
                ];
                $result = function () use ($urlRewriteReturnArray) {
                    return $urlRewriteReturnArray;
                };
            }
        }
        return $this->valueFactory->create($result);
    }

    /**
     * Find the canonical url passing through all redirects if any
     *
     * @param string $requestPath
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite|null
     */
    private function findCanonicalUrl(string $requestPath) : ?\Magento\UrlRewrite\Service\V1\Data\UrlRewrite
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
    private function findUrlFromRequestPath(string $requestPath) : ?\Magento\UrlRewrite\Service\V1\Data\UrlRewrite
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
    private function findUrlFromTargetPath(string $targetPath) : ?\Magento\UrlRewrite\Service\V1\Data\UrlRewrite
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
     * @return string
     */
    private function sanitizeType(string $type) : string
    {
        return strtoupper(str_replace('-', '_', $type));
    }
}
