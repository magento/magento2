<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewriteGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewriteGraphQl\Model\Resolver\UrlRewrite\CustomUrlLocatorInterface;
use Magento\Framework\GraphQl\Query\Uid;

abstract class AbstractEntityUrl implements ResolverInterface
{
    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var CustomUrlLocatorInterface
     */
    private $customUrlLocator;

    /**
     * @var int
     */
    private $redirectType;

    /**
     * @var Uid
     */
    private $idEncoder;

    /**
     * @param UrlFinderInterface $urlFinder
     * @param CustomUrlLocatorInterface $customUrlLocator
     * @param Uid $idEncoder
     */
    public function __construct(
        UrlFinderInterface $urlFinder,
        CustomUrlLocatorInterface $customUrlLocator,
        Uid $idEncoder
    ) {
        $this->urlFinder = $urlFinder;
        $this->customUrlLocator = $customUrlLocator;
        $this->idEncoder = $idEncoder;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['url']) || empty(trim($args['url']))) {
            throw new GraphQlInputException(__('"url" argument should be specified and not empty'));
        }

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $result = null;
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $urlParts = parse_url($args['url']);
        $url = $urlParts['path'] ?? $args['url'];
        if (substr($url, 0, 1) === '/' && $url !== '/') {
            $url = ltrim($url, '/');
        }
        $this->redirectType = 0;
        $customUrl = $this->customUrlLocator->locateUrl($url);
        $url = $customUrl ?: $url;
        $finalUrlRewrite = $this->findFinalUrl($url, $storeId);
        if ($finalUrlRewrite) {
            $relativeUrl = $finalUrlRewrite->getRequestPath();
            $resultArray = $this->rewriteCustomUrls($finalUrlRewrite, $storeId) ?? [
                    'id' => $finalUrlRewrite->getEntityId(),
                    'entity_uid' => $this->idEncoder->encode((string)$finalUrlRewrite->getEntityId()),
                    'canonical_url' => $relativeUrl,
                    'relative_url' => $relativeUrl,
                    'redirectCode' => $this->redirectType,
                    'redirect_code' => $this->redirectType,
                    'type' => $this->sanitizeType($finalUrlRewrite->getEntityType())
                ];
            if (!empty($urlParts['query'])) {
                $resultArray['relative_url'] .= '?' . $urlParts['query'];
            }

            if (empty($resultArray['id'])) {
                throw new GraphQlNoSuchEntityException(
                    __('No such entity found with matching URL key: %url', ['url' => $url])
                );
            }

            $result = $resultArray;
        }
        return $result;
    }

    /**
     * Handle custom urls with and without redirects
     *
     * @param UrlRewrite $finalUrlRewrite
     * @param int $storeId
     * @return array|null
     */
    private function rewriteCustomUrls(UrlRewrite $finalUrlRewrite, int $storeId): ?array
    {
        if ($finalUrlRewrite->getEntityType() === 'custom' || !($finalUrlRewrite->getEntityId() > 0)) {
            $finalCustomUrlRewrite = clone $finalUrlRewrite;
            $finalUrlRewrite = $this->findFinalUrl($finalCustomUrlRewrite->getTargetPath(), $storeId, true);
            $relativeUrl =
                $finalCustomUrlRewrite->getRedirectType() == 0
                    ? $finalCustomUrlRewrite->getRequestPath() : $finalUrlRewrite->getRequestPath();
            return [
                'id' => $finalUrlRewrite->getEntityId(),
                'entity_uid' => $this->idEncoder->encode((string)$finalUrlRewrite->getEntityId()),
                'canonical_url' => $relativeUrl,
                'relative_url' => $relativeUrl,
                'redirectCode' => $finalCustomUrlRewrite->getRedirectType(),
                'redirect_code' => $finalCustomUrlRewrite->getRedirectType(),
                'type' => $this->sanitizeType($finalUrlRewrite->getEntityType())
            ];
        }
        return null;
    }

    /**
     * Find the final url passing through all redirects if any
     *
     * @param string $requestPath
     * @param int $storeId
     * @param bool $findCustom
     * @return UrlRewrite|null
     */
    private function findFinalUrl(string $requestPath, int $storeId, bool $findCustom = false): ?UrlRewrite
    {
        $urlRewrite = $this->findUrlFromRequestPath($requestPath, $storeId);
        if ($urlRewrite) {
            $this->redirectType = $urlRewrite->getRedirectType();
            while ($urlRewrite && $urlRewrite->getRedirectType() > 0) {
                $urlRewrite = $this->findUrlFromRequestPath($urlRewrite->getTargetPath(), $storeId);
            }
        } else {
            $urlRewrite = $this->findUrlFromTargetPath($requestPath, $storeId);
        }
        if ($urlRewrite && ($findCustom && !$urlRewrite->getEntityId() && !$urlRewrite->getIsAutogenerated())) {
            $urlRewrite = $this->findUrlFromTargetPath($urlRewrite->getTargetPath(), $storeId);
        }

        return $urlRewrite;
    }

    /**
     * Find a url from a request url on the current store
     *
     * @param string $requestPath
     * @param int $storeId
     * @return UrlRewrite|null
     */
    private function findUrlFromRequestPath(string $requestPath, int $storeId): ?UrlRewrite
    {
        return $this->urlFinder->findOneByData(
            [
                'request_path' => $requestPath,
                'store_id' => $storeId
            ]
        );
    }

    /**
     * Find a url from a target url on the current store
     *
     * @param string $targetPath
     * @param int $storeId
     * @return UrlRewrite|null
     */
    private function findUrlFromTargetPath(string $targetPath, int $storeId): ?UrlRewrite
    {
        return $this->urlFinder->findOneByData(
            [
                'target_path' => $targetPath,
                'store_id' => $storeId
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
