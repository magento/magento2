<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param CollectionProvider $entityCollectionProvider
     * @param LinksInitializer $linkInitializer
     * @param Management $linkManagement
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ProductLink\CollectionProvider $entityCollectionProvider,
        \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks $linkInitializer,
        \Magento\Catalog\Model\ProductLink\Management $linkManagement
    ) {
        $this->productRepository = $productRepository;
        $this->entityCollectionProvider = $entityCollectionProvider;
        $this->linkInitializer = $linkInitializer;
        $this->linkManagement = $linkManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Catalog\Api\Data\ProductLinkInterface $entity)
    {
        $linkedProduct = $this->productRepository->get($entity->getLinkedProductSku());
        $product = $this->productRepository->get($entity->getProductSku());
        $links = $this->entityCollectionProvider->getCollection($product, $entity->getLinkType());

        $data = $entity->__toArray();
        foreach ($entity->getCustomAttributes() as $attribute) {
            $data[$attribute->getAttributeCode()] = $attribute->getValue();
        }
        $data['product_id'] = $linkedProduct->getId();
        $links[$linkedProduct->getId()] = $data;
        $this->linkInitializer->initializeLinks($product, [$entity->getLinkType() => $links]);
        try {
            $product->save();
        } catch (\Exception $exception) {
            throw new CouldNotSaveException('Invalid data provided for linked products');
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\Catalog\Api\Data\ProductLinkInterface $entity)
    {
        $linkedProduct = $this->productRepository->get($entity->getLinkedProductSku());
        $product = $this->productRepository->get($entity->getProductSku());
        $links = $this->entityCollectionProvider->getCollection($product, $entity->getLinkType());

        if (!isset($links[$linkedProduct->getId()])) {
            throw new NoSuchEntityException(
                sprintf(
                    'Product with SKU %s is not linked to product with SKU %s',
                    $entity->getLinkedProductSku(),
                    $entity->getProductSku()
                )
            );
        }
        //Remove product from the linked product list
        unset($links[$linkedProduct->getId()]);

        $this->linkInitializer->initializeLinks($product, [$entity->getLinkType() => $links]);
        try {
            $product->save();
        } catch (\Exception $exception) {
            throw new CouldNotSaveException('Invalid data provided for linked products');
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($productSku, $type, $linkedProductSku)
    {
        $linkItems = $this->linkManagement->getLinkedItemsByType($productSku, $type);
        /** @var \Magento\Catalog\Api\Data\ProductLinkInterface $linkItem */
        foreach ($linkItems as $linkItem) {
            if ($linkItem->getLinkedProductSku() == $linkedProductSku) {
                return $this->delete($linkItem);
            }
        }
        throw new NoSuchEntityException(
            'Product %s doesn\'t have linked %s as %s',
            [
                $productSku,
                $linkedProductSku,
                $type
            ]
        );
    }
}
