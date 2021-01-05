<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewriteGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewriteGraphQl\Model\Resolver\AbstractEntityUrl;
use Magento\UrlRewriteGraphQl\Model\Resolver\UrlRewrite\CustomUrlLocatorInterface;

/**
 * UrlRewrite field resolver, used for GraphQL request processing.
 */
class EntityUrl extends AbstractEntityUrl implements ResolverInterface
{
    /**
     * @var CustomUrlLocatorInterface
     */
    private $customUrlLocator;

    /**
     * @param UrlFinderInterface $urlFinder
     * @param CustomUrlLocatorInterface $customUrlLocator
     */
    public function __construct(
        UrlFinderInterface $urlFinder,
        CustomUrlLocatorInterface $customUrlLocator
    ) {
        parent::__construct($urlFinder);
        $this->customUrlLocator = $customUrlLocator;
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
        $url = $args['url'];
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
                    'canonical_url' => $relativeUrl,
                    'relative_url' => $relativeUrl,
                    'redirectCode' => $this->redirectType,
                    'type' => $this->sanitizeType($finalUrlRewrite->getEntityType())
                ];

            if (empty($resultArray['id'])) {
                throw new GraphQlNoSuchEntityException(
                    __('No such entity found with matching URL key: %url', ['url' => $url])
                );
            }

            $result = $resultArray;
        }
        return $result;
    }
}
