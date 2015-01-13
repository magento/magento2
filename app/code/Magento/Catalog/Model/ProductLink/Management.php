<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ProductLink;

use Magento\Catalog\Api\Data;
use Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks as LinksInitializer;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class Management implements \Magento\Catalog\Api\ProductLinkManagementInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CollectionProvider
     */
    protected $entityCollectionProvider;

    /**
     * @var LinksInitializer
     */
    protected $linkInitializer;

    /**
     * @var \Magento\Catalog\Api\Data\ProductLinkDataBuilder
     */
    protected $productLinkBuilder;

    /**
     * @var \Magento\Catalog\Model\Resource\Product
     */
    protected $productResource;

    /**
     * @var \Magento\Framework\Api\AttributeValueBuilder
     */
    protected $valueBuilder;

    /**
     * @var \Magento\Catalog\Model\Product\LinkTypeProvider
     */
    protected $linkTypeProvider;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param CollectionProvider $collectionProvider
     * @param Data\ProductLinkDataBuilder $productLinkBuilder
     * @param LinksInitializer $linkInitializer
     * @param \Magento\Catalog\Model\Resource\Product $productResource
     * @param \Magento\Framework\Api\AttributeValueBuilder $valueBuilder
     * @param \Magento\Catalog\Model\Product\LinkTypeProvider $linkTypeProvider
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        CollectionProvider $collectionProvider,
        \Magento\Catalog\Api\Data\ProductLinkDataBuilder $productLinkBuilder,
        LinksInitializer $linkInitializer,
        \Magento\Catalog\Model\Resource\Product $productResource,
        \Magento\Framework\Api\AttributeValueBuilder $valueBuilder,
        \Magento\Catalog\Model\Product\LinkTypeProvider $linkTypeProvider
    ) {
        $this->productRepository = $productRepository;
        $this->entityCollectionProvider = $collectionProvider;
        $this->productLinkBuilder = $productLinkBuilder;
        $this->productResource = $productResource;
        $this->linkInitializer = $linkInitializer;
        $this->valueBuilder = $valueBuilder;
        $this->linkTypeProvider = $linkTypeProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getLinkedItemsByType($productSku, $type)
    {
        $output = [];
        $product = $this->productRepository->get($productSku);
        try {
            $collection = $this->entityCollectionProvider->getCollection($product, $type);
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException('Unknown link type: ' . (string)$type);
        }
        foreach ($collection as $item) {
            $data = [
                'product_sku' => $product->getSku(),
                'link_type' => $type,
                'linked_product_sku' => $item['sku'],
                'linked_product_type' => $item['type'],
                'position' => $item['position'],
            ];
            $this->productLinkBuilder->populateWithArray($data);
            if (isset($item['custom_attributes'])) {
                foreach ($item['custom_attributes'] as $option) {
                    $this->productLinkBuilder->setCustomAttribute(
                        $option['attribute_code'],
                        $option['value']
                    );
                }
            }
            $output[] = $this->productLinkBuilder->create();
        }
        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function setProductLinks($productSku, $type, array $items)
    {
        $linkTypes = $this->linkTypeProvider->getLinkTypes();

        if (!isset($linkTypes[$type])) {
            throw new NoSuchEntityException(
                sprintf("Provided link type \"%s\" does not exist", $type)
            );
        }

        $product = $this->productRepository->get($productSku);
        $assignedSkuList = [];
        /** @var \Magento\Catalog\Api\Data\ProductLinkInterface $link */
        foreach ($items as $link) {
            $assignedSkuList[] = $link->getLinkedProductSku();
        }
        $linkedProductIds = $this->productResource->getProductsIdsBySkus($assignedSkuList);

        $links = [];
        /** @var \Magento\Catalog\Api\Data\ProductLinkInterface[] $items*/
        foreach ($items as $link) {
            $data = $link->__toArray();
            $linkedSku = $link->getLinkedProductSku();
            if (!isset($linkedProductIds[$linkedSku])) {
                throw new NoSuchEntityException(
                    sprintf("Product with SKU \"%s\" does not exist", $linkedSku)
                );
            }
            $data['product_id'] = $linkedProductIds[$linkedSku];
            $links[$linkedProductIds[$linkedSku]] = $data;
        }
        $this->linkInitializer->initializeLinks($product, [$type => $links]);
        try {
            $product->save();
        } catch (\Exception $exception) {
            throw new CouldNotSaveException('Invalid data provided for linked products');
        }
        return true;
    }
}
