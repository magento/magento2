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
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class SaveHandler
 * @since 2.1.0
 */
class SaveHandler
{
    /**
     * @var LinkTypeProvider
     * @since 2.1.0
     */
    protected $linkTypeProvider;

    /**
     * @var DataObjectProcessor
     * @since 2.1.0
     */
    protected $dataObjectProcessor;

    /**
     * @var MetadataPool
     * @since 2.1.0
     */
    protected $metadataPool;

    /**
     * @var ProductRepositoryInterface
     * @since 2.1.0
     */
    protected $productRepository;

    /**
     * @var Link
     * @since 2.1.0
     */
    protected $linkResource;

    /**
     * SaveHandler constructor.
     *
     * @param MetadataPool $metadataPool
     * @param ProductRepositoryInterface $productRepository
     * @param Link $linkResource
     * @param DataObjectProcessor $dataObjectProcessor
     * @param LinkTypeProvider $linkTypeProvider
     * @since 2.1.0
     */
    public function __construct(
        MetadataPool $metadataPool,
        ProductRepositoryInterface $productRepository,
        Link $linkResource,
        DataObjectProcessor $dataObjectProcessor,
        LinkTypeProvider $linkTypeProvider
    ) {
        $this->metadataPool = $metadataPool;
        $this->productRepository = $productRepository;
        $this->linkResource = $linkResource;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->linkTypeProvider = $linkTypeProvider;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return object
     * @throws CouldNotSaveException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function execute($entityType, $entity)
    {
        /**
         * @var $entity \Magento\Catalog\Api\Data\ProductLinkInterface
         */
        $linkedProduct = $this->productRepository->get($entity->getLinkedProductSku());
        $product = $this->productRepository->get($entity->getSku());
        $links = [];
        $extensions = $this->dataObjectProcessor->buildOutputDataArray(
            $entity->getExtensionAttributes(),
            \Magento\Catalog\Api\Data\ProductLinkExtensionInterface::class
        );
        $extensions = is_array($extensions) ? $extensions : [];
        $data = $entity->__toArray();
        foreach ($extensions as $attributeCode => $attribute) {
            $data[$attributeCode] = $attribute;
        }
        unset($data['extension_attributes']);
        $data['product_id'] = $linkedProduct->getId();
        $links[$linkedProduct->getId()] = $data;

        try {
            $linkTypesToId = $this->linkTypeProvider->getLinkTypes();
            $prodyctHydrator = $this->metadataPool->getHydrator(ProductInterface::class);
            $productData = $prodyctHydrator->extract($product);
            $this->linkResource->saveProductLinks(
                $productData[$this->metadataPool->getMetadata(ProductInterface::class)->getLinkField()],
                $links,
                $linkTypesToId[$entity->getLinkType()]
            );
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Invalid data provided for linked products'));
        }
        return $entity;
    }
}
