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
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Class Repository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Repository implements \Magento\Catalog\Api\ProductLinkRepositoryInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Relation
     */
    protected $catalogProductRelation;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Link
     */
    protected $linkResource;

    /**
     * @var LinkTypeProvider
     */
    protected $linkTypeProvider;

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
     * @var ProductLinkInterfaceFactory
     */
    protected $productLinkFactory;

    /**
     * @var ProductLinkExtensionFactory
     */
    protected $productLinkExtensionFactory;

    /**
     * Repository constructor.
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param CollectionProvider $entityCollectionProvider
     * @param LinksInitializer $linkInitializer
     * @param Management $linkManagement
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param ProductLinkInterfaceFactory|null $productLinkFactory
     * @param ProductLinkExtensionFactory|null $productLinkExtensionFactory
     * @throws \RuntimeException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
        if (null === $productLinkFactory) {
            $productLinkFactory = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Catalog\Api\Data\ProductLinkInterfaceFactory::class);
        }
        if (null === $productLinkExtensionFactory) {
            $productLinkExtensionFactory = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Catalog\Api\Data\ProductLinkExtensionFactory::class);
        }
        $this->productLinkFactory = $productLinkFactory;
        $this->productLinkExtensionFactory = $productLinkExtensionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Catalog\Api\Data\ProductLinkInterface $entity)
    {
        $linkedProduct = $this->productRepository->get($entity->getLinkedProductSku());
        $product = $this->productRepository->get($entity->getSku());
        $links = [];
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
                        $productLinkExtension = $this->productLinkExtensionFactory->create();
                    }
                    foreach ($item['custom_attributes'] as $option) {
                        $name = $option['attribute_code'];
                        $value = $option['value'];
                        $setterName = 'set' . SimpleDataObjectConverter::snakeCaseToUpperCamelCase($name);
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
     */
    private function getLinkResource()
    {
        if (null === $this->linkResource) {
            $this->linkResource = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Catalog\Model\ResourceModel\Product\Link');
        }
        return $this->linkResource;
    }

    /**
     * @return LinkTypeProvider
     */
    private function getLinkTypeProvider()
    {
        if (null === $this->linkTypeProvider) {
            $this->linkTypeProvider = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Catalog\Model\Product\LinkTypeProvider');
        }
        return $this->linkTypeProvider;
    }

    /**
     * @return \Magento\Framework\EntityManager\MetadataPool
     */
    private function getMetadataPool()
    {
        if (null === $this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Framework\EntityManager\MetadataPool');
        }
        return $this->metadataPool;
    }
}
