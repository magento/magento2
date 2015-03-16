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
     * @var \Magento\Catalog\Api\Data\ProductLinkInterfaceFactory
     */
    protected $productLinkFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Product
     */
    protected $productResource;

    /**
     * @var \Magento\Catalog\Model\Product\LinkTypeProvider
     */
    protected $linkTypeProvider;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param CollectionProvider $collectionProvider
     * @param \Magento\Catalog\Api\Data\ProductLinkInterfaceFactory $productLinkFactory
     * @param LinksInitializer $linkInitializer
     * @param \Magento\Catalog\Model\Resource\Product $productResource
     * @param \Magento\Catalog\Model\Product\LinkTypeProvider $linkTypeProvider
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        CollectionProvider $collectionProvider,
        \Magento\Catalog\Api\Data\ProductLinkInterfaceFactory $productLinkFactory,
        LinksInitializer $linkInitializer,
        \Magento\Catalog\Model\Resource\Product $productResource,
        \Magento\Catalog\Model\Product\LinkTypeProvider $linkTypeProvider
    ) {
        $this->productRepository = $productRepository;
        $this->entityCollectionProvider = $collectionProvider;
        $this->productLinkFactory = $productLinkFactory;
        $this->productResource = $productResource;
        $this->linkInitializer = $linkInitializer;
        $this->linkTypeProvider = $linkTypeProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getLinkedItemsByType($sku, $type)
    {
        $output = [];
        $product = $this->productRepository->get($sku);
        try {
            $collection = $this->entityCollectionProvider->getCollection($product, $type);
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException(__('Unknown link type: %1', (string)$type));
        }
        foreach ($collection as $item) {
            /** @var \Magento\Catalog\Api\Data\ProductLinkInterface $productLink */
            $productLink = $this->productLinkFactory->create();
            $productLink->setProductSku($product->getSku())
                ->setLinkType($type)
                ->setLinkedProductSku($item['sku'])
                ->setLinkedProductType($item['type'])
                ->setPosition($item['position']);
            if (isset($item['custom_attributes'])) {
                foreach ($item['custom_attributes'] as $option) {
                    $productLink->getExtensionAttributes()->setQty($option['value']);
                }
            }
            $output[] = $productLink;
        }
        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function setProductLinks($sku, $type, array $items)
    {
        $linkTypes = $this->linkTypeProvider->getLinkTypes();

        if (!isset($linkTypes[$type])) {
            throw new NoSuchEntityException(
                __('Provided link type "%1" does not exist', $type)
            );
        }

        $product = $this->productRepository->get($sku);
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
                    __('Product with SKU "%1" does not exist', $linkedSku)
                );
            }
            $data['product_id'] = $linkedProductIds[$linkedSku];
            $links[$linkedProductIds[$linkedSku]] = $data;
        }
        $this->linkInitializer->initializeLinks($product, [$type => $links]);
        try {
            $product->save();
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Invalid data provided for linked products'));
        }
        return true;
    }
}
