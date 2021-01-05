<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewriteGraphQl\Model\Resolver;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewriteGraphQl\Model\Resolver\UrlRewrite\CustomUrlLocatorInterface;
use Magento\CmsGraphQl\Model\Resolver\DataProvider\Page as PageDataProvider;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ExtractDataFromCategoryTree;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CategoryTree as CategoryTreeDataProvider;
use Magento\Catalog\Model\CategoryRepository;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class Route implements ResolverInterface
{
    const CMS_PAGE = 'CMS_PAGE';
    const PRODUCT = 'PRODUCT';
    const CATEGORY = 'CATEGORY';
    /**
     * @var PageDataProvider
     */
    private $pageDataProvider;

    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var CustomUrlLocatorInterface
     */
    private $customUrlLocator;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var ExtractDataFromCategoryTree
     */
    private $extractDataFromCategoryTree;

    /**
     * @var CategoryTreeDataProvider
     */
    private $categoryTree;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
    * @param UrlFinderInterface $urlFinder
    * @param CustomUrlLocatorInterface $customUrlLocator
    */
    public function __construct(
        UrlFinderInterface $urlFinder,
        CustomUrlLocatorInterface $customUrlLocator,
        ProductRepository $productRepository,
        CategoryTreeDataProvider $categoryTree,
        ExtractDataFromCategoryTree $extractDataFromCategoryTree,
        PageDataProvider $pageDataProvider,
        CategoryRepository $categoryRepository
    ) {
        $this->urlFinder = $urlFinder;
        $this->customUrlLocator = $customUrlLocator;
        $this->productRepository = $productRepository;
        $this->categoryTree = $categoryTree;
        $this->extractDataFromCategoryTree = $extractDataFromCategoryTree;
        $this->pageDataProvider = $pageDataProvider;
        $this->categoryRepository = $categoryRepository;
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

            if ($resultArray['type'] == self::CMS_PAGE) {
                $result = $this->pageDataProvider->getDataByPageId((int)$resultArray['id']);
                $result['type_id'] = self::CMS_PAGE;
            } else if ($resultArray['type'] == self::CATEGORY) {
                $categoryId = (int)$resultArray['id'];
                $categoty = $this->categoryRepository->get($categoryId);

                $categoriesTree = $this->categoryTree->getTree($info, $categoryId);
                if (empty($categoriesTree) || ($categoriesTree->count() == 0)) {
                    throw new GraphQlNoSuchEntityException(__('Category doesn\'t exist'));
                }

                $result = current($this->extractDataFromCategoryTree->execute($categoriesTree));

                $result['meta_title'] = $categoty->getData()['meta_title'] ?? null;
                $result['meta_keywords'] = $categoty->getData()['meta_keywords'] ?? null;
                $result['meta_description'] = $categoty->getData()['meta_description'] ?? null;
                $result['type_id'] = self::CATEGORY;
            } else if ($resultArray['type'] == self::PRODUCT) {
                $product = $this->productRepository->getById($resultArray['id']);
                $result = $product->getData();
                $result['model'] = $product;
            }

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
                'canonical_url' => $relativeUrl,
                'relative_url' => $relativeUrl,
                'redirectCode' => $finalCustomUrlRewrite->getRedirectType(),
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
