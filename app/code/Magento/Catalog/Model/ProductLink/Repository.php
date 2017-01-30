<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ProductLink;

use Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks as LinksInitializer;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\Data;

class Repository implements \Magento\Catalog\Api\ProductLinkRepositoryInterface
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
     * @var Management
     */
    protected $linkManagement;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param CollectionProvider $entityCollectionProvider
     * @param LinksInitializer $linkInitializer
     * @param Management $linkManagement
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ProductLink\CollectionProvider $entityCollectionProvider,
        \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks $linkInitializer,
        \Magento\Catalog\Model\ProductLink\Management $linkManagement,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
    ) {
        $this->productRepository = $productRepository;
        $this->entityCollectionProvider = $entityCollectionProvider;
        $this->linkInitializer = $linkInitializer;
        $this->linkManagement = $linkManagement;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Catalog\Api\Data\ProductLinkInterface $entity)
    {
        $linkedProduct = $this->productRepository->get($entity->getLinkedProductSku());
        $product = $this->productRepository->get($entity->getSku());
        $links = $this->entityCollectionProvider->getCollection($product, $entity->getLinkType());
        $extensions = $this->dataObjectProcessor->buildOutputDataArray(
            $entity->getExtensionAttributes(),
            'Magento\Catalog\Api\Data\ProductLinkExtensionInterface'
        );
        $extensions = is_array($extensions) ? $extensions : [];
        $data = $entity->__toArray();
        foreach ($extensions as $attributeCode => $attribute) {
            $data[$attributeCode] = $attribute;
        }
        unset($data['extension_attributes']);
        $data['product_id'] = $linkedProduct->getId();
        $links[$linkedProduct->getId()] = $data;
        $this->linkInitializer->initializeLinks($product, [$entity->getLinkType() => $links]);
        try {
            $product->save();
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Invalid data provided for linked products'));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\Catalog\Api\Data\ProductLinkInterface $entity)
    {
        $linkedProduct = $this->productRepository->get($entity->getLinkedProductSku());
        $product = $this->productRepository->get($entity->getSku());
        $links = $this->entityCollectionProvider->getCollection($product, $entity->getLinkType());

        if (!isset($links[$linkedProduct->getId()])) {
            throw new NoSuchEntityException(
                __(
                    'Product with SKU %1 is not linked to product with SKU %2',
                    $entity->getLinkedProductSku(),
                    $entity->getSku()
                )
            );
        }
        //Remove product from the linked product list
        unset($links[$linkedProduct->getId()]);

        $this->linkInitializer->initializeLinks($product, [$entity->getLinkType() => $links]);
        try {
            $product->save();
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Invalid data provided for linked products'));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($sku, $type, $linkedProductSku)
    {
        $linkItems = $this->linkManagement->getLinkedItemsByType($sku, $type);
        /** @var \Magento\Catalog\Api\Data\ProductLinkInterface $linkItem */
        foreach ($linkItems as $linkItem) {
            if ($linkItem->getLinkedProductSku() == $linkedProductSku) {
                return $this->delete($linkItem);
            }
        }
        throw new NoSuchEntityException(
            __(
                'Product %1 doesn\'t have linked %2 as %3',
                [
                    $sku,
                    $linkedProductSku,
                    $type
                ]
            )
        );
    }
}
