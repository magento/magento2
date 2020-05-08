<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\ProductLink;

use Magento\Catalog\Model\Product\LinkTypeProvider;
use Magento\Catalog\Model\ProductLink\Data\ListCriteria;
use Magento\Catalog\Model\ProductLink\Data\ListResult;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\Data\ProductLinkExtensionFactory;
use Magento\Framework\Exception\InputException;

/**
 * Search for product links by criteria.
 *
 * Batch contract for getting product links.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductLinkQuery
{
    /**
     * @var LinkTypeProvider
     */
    private $linkTypeProvider;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var CollectionProvider
     */
    private $collectionProvider;

    /**
     * @var ProductLinkInterfaceFactory
     */
    private $productLinkFactory;

    /**
     * @var ProductLinkExtensionFactory
     */
    private $productLinkExtensionFactory;

    /**
     * @param LinkTypeProvider $linkTypeProvider
     * @param ProductRepository $productRepository
     * @param SearchCriteriaBuilder $criteriaBuilder
     * @param CollectionProvider $collectionProvider
     * @param ProductLinkInterfaceFactory $productLinkFactory
     * @param ProductLinkExtensionFactory $productLinkExtensionFactory
     */
    public function __construct(
        LinkTypeProvider $linkTypeProvider,
        ProductRepository $productRepository,
        SearchCriteriaBuilder $criteriaBuilder,
        CollectionProvider $collectionProvider,
        ProductLinkInterfaceFactory $productLinkFactory,
        ProductLinkExtensionFactory $productLinkExtensionFactory
    ) {
        $this->linkTypeProvider = $linkTypeProvider;
        $this->productRepository = $productRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->collectionProvider = $collectionProvider;
        $this->productLinkFactory = $productLinkFactory;
        $this->productLinkExtensionFactory = $productLinkExtensionFactory;
    }

    /**
     * Extract all link types requested.
     *
     * @param \Magento\Catalog\Model\ProductLink\Data\ListCriteriaInterface[] $criteria
     * @return string[]
     */
    private function extractRequestedLinkTypes(array $criteria): array
    {
        $linkTypes = $this->linkTypeProvider->getLinkTypes();
        $linkTypesToLoad = [];
        foreach ($criteria as $listCriteria) {
            if ($listCriteria->getLinkTypes() === null) {
                //All link types are to be returned.
                $linkTypesToLoad = null;
                break;
            }
            $linkTypesToLoad[] = $listCriteria->getLinkTypes();
        }
        if ($linkTypesToLoad !== null) {
            if (count($linkTypesToLoad) === 1) {
                $linkTypesToLoad = $linkTypesToLoad[0];
            } else {
                $linkTypesToLoad = array_merge(...$linkTypesToLoad);
            }
            $linkTypesToLoad = array_flip($linkTypesToLoad);
            $linkTypes = array_filter(
                $linkTypes,
                function (string $code) use ($linkTypesToLoad) {
                    return array_key_exists($code, $linkTypesToLoad);
                },
                ARRAY_FILTER_USE_KEY
            );
        }

        return $linkTypes;
    }

    /**
     * Load products links were requested for.
     *
     * @param \Magento\Catalog\Model\ProductLink\Data\ListCriteriaInterface[] $criteria
     * @return \Magento\Catalog\Model\Product[] Keys are SKUs.
     */
    private function loadProductsByCriteria(array $criteria): array
    {
        $products = [];
        $skusToLoad = [];
        foreach ($criteria as $listCriteria) {
            if ($listCriteria instanceof ListCriteria
                && $listCriteria->getBelongsToProduct()
            ) {
                $products[$listCriteria->getBelongsToProduct()->getSku()] = $listCriteria->getBelongsToProduct();
            } else {
                $skusToLoad[] = $listCriteria->getBelongsToProductSku();
            }
        }

        $skusToLoad = array_filter(
            $skusToLoad,
            function ($sku) use ($products) {
                return !array_key_exists($sku, $products);
            }
        );
        if ($skusToLoad) {
            $loaded = $this->productRepository->getList(
                $this->criteriaBuilder->addFilter('sku', $skusToLoad, 'in')->create()
            );
            foreach ($loaded->getItems() as $product) {
                $products[$product->getSku()] = $product;
            }
        }

        return $products;
    }

    /**
     * Convert links data to DTOs.
     *
     * @param string $productSku SKU of the root product.
     * @param array[] $linksData Links data returned from collection.
     * @param string[]|null $acceptedTypes Link types that are accepted.
     * @return \Magento\Catalog\Api\Data\ProductLinkInterface[]
     */
    private function convertLinksData(string $productSku, array $linksData, ?array $acceptedTypes): array
    {
        $list = [];
        foreach ($linksData as $linkData) {
            if ($acceptedTypes && !in_array($linkData['link_type'], $acceptedTypes, true)) {
                continue;
            }
            /** @var \Magento\Catalog\Api\Data\ProductLinkInterface $productLink */
            $productLink = $this->productLinkFactory->create();
            $productLink->setSku($productSku)
                ->setLinkType($linkData['link_type'])
                ->setLinkedProductSku($linkData['sku'])
                ->setLinkedProductType($linkData['type'])
                ->setPosition($linkData['position']);
            if (isset($linkData['custom_attributes'])) {
                $productLinkExtension = $productLink->getExtensionAttributes();
                if ($productLinkExtension === null) {
                    /** @var \Magento\Catalog\Api\Data\ProductLinkExtensionInterface $productLinkExtension */
                    $productLinkExtension = $this->productLinkExtensionFactory->create();
                }
                foreach ($linkData['custom_attributes'] as $option) {
                    $name = $option['attribute_code'];
                    $value = $option['value'];
                    $setterName = 'set' . SimpleDataObjectConverter::snakeCaseToUpperCamelCase($name);
                    // Check if setter exists
                    if (method_exists($productLinkExtension, $setterName)) {
                        // phpcs:ignore Magento2.Functions.DiscouragedFunction
                        call_user_func([$productLinkExtension, $setterName], $value);
                    }
                }
                $productLink->setExtensionAttributes($productLinkExtension);
            }
            $list[] = $productLink;
        }

        return $list;
    }

    /**
     * Get list of product links found by criteria.
     *
     * Results are returned in the same order as criteria items.
     *
     * @param \Magento\Catalog\Model\ProductLink\Data\ListCriteriaInterface[] $criteria
     * @return \Magento\Catalog\Model\ProductLink\Data\ListResultInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function search(array $criteria): array
    {
        if (!$criteria) {
            throw InputException::requiredField('criteria');
        }

        //Requested link types.
        $linkTypes = $this->extractRequestedLinkTypes($criteria);
        //Requested products.
        $products = $this->loadProductsByCriteria($criteria);
        //Map of products and their linked products' data.
        $map = $this->collectionProvider->getMap($products, $linkTypes);

        //Batch contract results.
        $results = [];
        foreach ($criteria as $listCriteria) {
            $productSku = $listCriteria->getBelongsToProductSku();
            if (!array_key_exists($productSku, $map)) {
                $results[] = new ListResult([], null);
                continue;
            }
            try {
                $list = $this->convertLinksData($productSku, $map[$productSku], $listCriteria->getLinkTypes());
                $results[] = new ListResult($list, null);
            } catch (\Throwable $error) {
                $results[] = new ListResult(null, $error);
            }
        }

        return $results;
    }
}
