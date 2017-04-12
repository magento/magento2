<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Product\Link;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\LinkTypeProvider;
use Magento\Catalog\Model\ResourceModel\Product\Link;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;

/**
 * Class DeleteHandler
 */
class DeleteHandler
{
    /**
     * @var LinkTypeProvider
     */
    protected $linkTypeProvider;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Link
     */
    protected $linkResource;

    /**
     * DeleteHandler constructor.
     *
     * @param MetadataPool $metadataPool
     * @param ProductRepositoryInterface $productRepository
     * @param Link $linkResource
     * @param LinkTypeProvider $linkTypeProvider
     */
    public function __construct(
        MetadataPool $metadataPool,
        ProductRepositoryInterface $productRepository,
        Link $linkResource,
        LinkTypeProvider $linkTypeProvider
    ) {
        $this->metadataPool = $metadataPool;
        $this->productRepository = $productRepository;
        $this->linkResource = $linkResource;
        $this->linkTypeProvider = $linkTypeProvider;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return object
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entityType, $entity)
    {
        $linkedProduct = $this->productRepository->get($entity->getLinkedProductSku());
        $product = $this->productRepository->get($entity->getSku());
        $linkTypesToId = $this->linkTypeProvider->getLinkTypes();
        $prodyctHydrator = $this->metadataPool->getHydrator(ProductInterface::class);
        $productData = $prodyctHydrator->extract($product);
        $linkId = $this->linkResource->getProductLinkId(
            $productData[$this->metadataPool->getMetadata(ProductInterface::class)->getLinkField()],
            $linkedProduct->getId(),
            $linkTypesToId[$entity->getLinkType()]
        );

        if (!$linkId) {
            throw new NoSuchEntityException(
                __(
                    'Product with SKU %1 is not linked to product with SKU %2',
                    $entity->getLinkedProductSku(),
                    $entity->getSku()
                )
            );
        }

        try {
            $this->linkResource->deleteProductLink($linkId);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__('Invalid data provided for linked products'));
        }
    }
}
