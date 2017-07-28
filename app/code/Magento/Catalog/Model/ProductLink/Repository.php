<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ProductLink;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\Data\ProductLinkExtensionFactory;
use Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks as LinksInitializer;
use Magento\Catalog\Model\Product\LinkTypeProvider;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\App\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Repository implements \Magento\Catalog\Api\ProductLinkRepositoryInterface
{
    /**
     * @var MetadataPool
     * @since 2.1.0
     */
    protected $metadataPool;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Relation
     * @since 2.1.0
     */
    protected $catalogProductRelation;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Link
     * @since 2.1.0
     */
    protected $linkResource;

    /**
     * @var LinkTypeProvider
     * @since 2.1.0
     */
    protected $linkTypeProvider;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     * @since 2.0.0
     */
    protected $productRepository;

    /**
     * @var CollectionProvider
     * @since 2.0.0
     */
    protected $entityCollectionProvider;

    /**
     * @var LinksInitializer
     * @since 2.0.0
     */
    protected $linkInitializer;

    /**
     * @var Management
     * @since 2.0.0
     */
    protected $linkManagement;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     * @since 2.0.0
     */
    protected $dataObjectProcessor;

    /**
     * @var ProductLinkInterfaceFactory
     * @since 2.1.0
     */
    protected $productLinkFactory;

    /**
     * @var ProductLinkExtensionFactory
     * @since 2.1.0
     */
    protected $productLinkExtensionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param CollectionProvider $entityCollectionProvider
     * @param LinksInitializer $linkInitializer
     * @param Management $linkManagement
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Catalog\Api\Data\ProductLinkInterfaceFactory|null $productLinkFactory
     * @param \Magento\Catalog\Api\Data\ProductLinkExtensionFactory|null $productLinkExtensionFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ProductLink\CollectionProvider $entityCollectionProvider,
        \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks $linkInitializer,
        \Magento\Catalog\Model\ProductLink\Management $linkManagement,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Catalog\Api\Data\ProductLinkInterfaceFactory $productLinkFactory = null,
        \Magento\Catalog\Api\Data\ProductLinkExtensionFactory $productLinkExtensionFactory = null
    ) {
        $this->productRepository = $productRepository;
        $this->entityCollectionProvider = $entityCollectionProvider;
        $this->linkInitializer = $linkInitializer;
        $this->linkManagement = $linkManagement;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->productLinkFactory = $productLinkFactory ?: ObjectManager::getInstance()
            ->get(\Magento\Catalog\Api\Data\ProductLinkInterfaceFactory::class);
        $this->productLinkExtensionFactory = $productLinkExtensionFactory ?: ObjectManager::getInstance()
            ->get(\Magento\Catalog\Api\Data\ProductLinkExtensionFactory::class);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function save(\Magento\Catalog\Api\Data\ProductLinkInterface $entity)
    {
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
            $linkTypesToId = $this->getLinkTypeProvider()->getLinkTypes();
            $productData = $this->getMetadataPool()->getHydrator(ProductInterface::class)->extract($product);
            $this->getLinkResource()->saveProductLinks(
                $productData[$this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField()],
                $links,
                $linkTypesToId[$entity->getLinkType()]
            );
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Invalid data provided for linked products'));
        }
        return true;
    }

    /**
     * Get product links list
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Catalog\Api\Data\ProductLinkInterface[]
     * @since 2.1.0
     */
    public function getList(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $output = [];
        $linkTypes = $this->getLinkTypeProvider()->getLinkTypes();
        foreach (array_keys($linkTypes) as $linkTypeName) {
            $collection = $this->entityCollectionProvider->getCollection($product, $linkTypeName);
            foreach ($collection as $item) {
                /** @var \Magento\Catalog\Api\Data\ProductLinkInterface $productLink */
                $productLink = $this->productLinkFactory->create();
                $productLink->setSku($product->getSku())
                    ->setLinkType($linkTypeName)
                    ->setLinkedProductSku($item['sku'])
                    ->setLinkedProductType($item['type'])
                    ->setPosition($item['position']);
                if (isset($item['custom_attributes'])) {
                    $productLinkExtension = $productLink->getExtensionAttributes();
                    if ($productLinkExtension === null) {
                        $productLinkExtension = $this->productLinkExtensionFactory()->create();
                    }
                    foreach ($item['custom_attributes'] as $option) {
                        $name = $option['attribute_code'];
                        $value = $option['value'];
                        $setterName = 'set'.ucfirst($name);
                        // Check if setter exists
                        if (method_exists($productLinkExtension, $setterName)) {
                            call_user_func([$productLinkExtension, $setterName], $value);
                        }
                    }
                    $productLink->setExtensionAttributes($productLinkExtension);
                }
                $output[] = $productLink;
            }
        }
        return $output;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function delete(\Magento\Catalog\Api\Data\ProductLinkInterface $entity)
    {
        $linkedProduct = $this->productRepository->get($entity->getLinkedProductSku());
        $product = $this->productRepository->get($entity->getSku());
        $linkTypesToId = $this->getLinkTypeProvider()->getLinkTypes();
        $productData = $this->getMetadataPool()->getHydrator(ProductInterface::class)->extract($product);
        $linkId = $this->getLinkResource()->getProductLinkId(
            $productData[$this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField()],
            $linkedProduct->getId(),
            $linkTypesToId[$entity->getLinkType()]
        );

        if (!$linkId) {
            throw new NoSuchEntityException(
                __(
                    'Product with SKU \'%1\' is not linked to product with SKU \'%2\'',
                    $entity->getLinkedProductSku(),
                    $entity->getSku()
                )
            );
        }

        try {
            $this->getLinkResource()->deleteProductLink($linkId);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Invalid data provided for linked products'));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Link
     * @since 2.1.0
     */
    private function getLinkResource()
    {
        if (null === $this->linkResource) {
            $this->linkResource = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Catalog\Model\ResourceModel\Product\Link::class);
        }
        return $this->linkResource;
    }

    /**
     * @return LinkTypeProvider
     * @since 2.1.0
     */
    private function getLinkTypeProvider()
    {
        if (null === $this->linkTypeProvider) {
            $this->linkTypeProvider = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Catalog\Model\Product\LinkTypeProvider::class);
        }
        return $this->linkTypeProvider;
    }

    /**
     * @return \Magento\Framework\EntityManager\MetadataPool
     * @since 2.1.0
     */
    private function getMetadataPool()
    {
        if (null === $this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\MetadataPool::class);
        }
        return $this->metadataPool;
    }
}
