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
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CmsGraphQl\Model\Resolver\DataProvider\Page as PageDataProvider;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ExtractDataFromCategoryTree;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CategoryTree as CategoryTreeDataProvider;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\CategoryRepository;
use Magento\UrlRewriteGraphQl\Model\Resolver\AbstractEntityUrl;
use Magento\UrlRewriteGraphQl\Model\Resolver\UrlRewrite\CustomUrlLocatorInterface;

class Route  extends AbstractEntityUrl implements ResolverInterface
{
    const CMS_PAGE = 'CMS_PAGE';
    const PRODUCT = 'PRODUCT';
    const CATEGORY = 'CATEGORY';
    /**
     * @var PageDataProvider
     */
    private $pageDataProvider;

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
     * @param ProductRepository $productRepository
     * @param CategoryTreeDataProvider $categoryTree
     * @param ExtractDataFromCategoryTree $extractDataFromCategoryTree
     * @param PageDataProvider $pageDataProvider
     * @param CategoryRepository $categoryRepository
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
        parent::__construct($urlFinder);
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
}
