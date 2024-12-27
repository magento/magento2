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
        $this->validateArgs($args);
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $result = null;

        $urlParts = $this->parseUrl($args['url']);
        $url = $urlParts['path'];
        $customUrl = $this->customUrlLocator->locateUrl($url);
        $url = $customUrl ?: $url;
        $redirectType = 0;
        $urlRewrite = $this->findUrlFromRequestPath($url, $storeId);
        if ($urlRewrite) {
            $redirectType = $urlRewrite->getRedirectType();
        } else {
            $urlRewrite = $this->findUrlFromTargetPath($url, $storeId);
        }
        if ($urlRewrite) {
            $finalUrlRewrite = $this->findFinalUrl($urlRewrite);
            $entityId = (int) $finalUrlRewrite->getEntityId();
            $entityType = $finalUrlRewrite->getEntityType();
            if (!$entityId) {
                $entityUrlRewrite = $this->findUrlFromTargetPath($finalUrlRewrite->getTargetPath(), $storeId);
                $entityId = (int) $entityUrlRewrite->getEntityId();
                $entityType = $entityUrlRewrite->getEntityType();
            }
            if ($redirectType === 0 && !$entityId) {
                throw new GraphQlNoSuchEntityException(
                    __('No such entity found with matching URL key: %url', ['url' => $url])
                );
            }
            $relativeUrl = $redirectType > 0
                ? $this->getRedirectPath($finalUrlRewrite)
                : $urlRewrite->getRequestPath();

            if (!empty($urlParts['query'])) {
                $relativeUrl .= '?' . $urlParts['query'];
            }
            $result = $this->getData($relativeUrl, $redirectType, $entityType, $entityId);
        }

        return $result;
    }

    /**
     * Format and returns url data
     *
     * @param string $url
     * @param int $redirectType
     * @param string|null $entityType
     * @param int|null $entityId
     * @return array
     */
    private function getData(string $url, int $redirectType, ?string $entityType, ?int $entityId)
    {
        return [
            'id' => $entityId,
            'entity_uid' => $this->idEncoder->encode((string)$entityId),
            'canonical_url' => $url,
            'relative_url' => $url,
            'redirectCode' => $redirectType,
            'redirect_code' => $redirectType,
            'type' => $entityId ? $this->sanitizeType($entityType) : null
        ];
    }

    /**
     * Find the final url passing through all redirects if any
     *
     * @param UrlRewrite $urlRewrite
     * @return UrlRewrite
     */
    private function findFinalUrl(UrlRewrite $urlRewrite): UrlRewrite
    {
        do {
            $nextUrlRewrite = $this->findUrlFromRequestPath(
                $urlRewrite->getTargetPath(),
                (int) $urlRewrite->getStoreId()
            );
            if ($nextUrlRewrite) {
                $urlRewrite = $nextUrlRewrite;
            }
        } while ($nextUrlRewrite);

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
    private function sanitizeType(string $type): string
    {
        return strtoupper(str_replace('-', '_', $type));
    }

    /**
     * Returns url components
     *
     * @param string $url
     * @return array
     */
    private function parseUrl(string $url): array
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $urlParts = parse_url($url);
        if (!is_array($urlParts)) {
            $urlParts = [];
            $urlParts['path'] = $url;
        }
        if (substr($urlParts['path'], 0, 1) === '/' && $urlParts['path'] !== '/') {
            $urlParts['path'] = ltrim($urlParts['path'], '/');
        }

        return $urlParts;
    }

    /**
     * Get path to redirect to
     *
     * @param UrlRewrite $urlRewrite
     * @return string
     */
    private function getRedirectPath(UrlRewrite $urlRewrite): string
    {
        return $urlRewrite->getRedirectType() > 0
            ? $urlRewrite->getTargetPath()
            : $urlRewrite->getRequestPath();
    }

    /**
     * Validates input
     *
     * @param array $args
     * @return void
     * @throws GraphQlInputException
     */
    private function validateArgs(array $args): void
    {
        if (!isset($args['url']) || empty(trim($args['url']))) {
            throw new GraphQlInputException(__('"url" argument should be specified and not empty'));
        }
    }
}
