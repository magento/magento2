<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\ProductLink;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\Data\ProductLinkExtensionFactory;
use Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks as LinksInitializer;
use Magento\Catalog\Model\Product\LinkTypeProvider;
use Magento\Catalog\Model\ProductLink\Data\ListCriteria;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\App\ObjectManager;

/**
 * Product link entity repository.
 *
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
     * @deprecated Not used anymore.
     * @see query
     */
    protected $entityCollectionProvider;

    /**
     * @var LinksInitializer
     * @deprecated Not used.
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
     * @deprecated Not used anymore, search delegated.
     * @see getList()
     */
    protected $productLinkFactory;

    /**
     * @var ProductLinkExtensionFactory
     * @deprecated Not used anymore, search delegated.
     * @see getList()
     */
    protected $productLinkExtensionFactory;

    /**
     * @var ProductLinkQuery
     */
    private $query;

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
     * @param ProductLinkQuery|null $query
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ProductLink\CollectionProvider $entityCollectionProvider,
        \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks $linkInitializer,
        \Magento\Catalog\Model\ProductLink\Management $linkManagement,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Catalog\Api\Data\ProductLinkInterfaceFactory $productLinkFactory = null,
        \Magento\Catalog\Api\Data\ProductLinkExtensionFactory $productLinkExtensionFactory = null,
        ?ProductLinkQuery $query = null
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
        $this->query = $query ?? ObjectManager::getInstance()->get(ProductLinkQuery::class);
    }

    /**
     * @inheritDoc
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
            throw new CouldNotSaveException(__('The linked products data is invalid. Verify the data and try again.'));
        }
        return true;
    }

    /**
     * Get product links list
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Api\Data\ProductLinkInterface[]
     */
    public function getList(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        if (!$product->getSku() || !$product->getId()) {
            return $product->getProductLinks();
        }
        $criteria = new ListCriteria($product->getSku(), null, $product);
        $result = $this->query->search([$criteria])[0];

        if ($result->getError()) {
            throw $result->getError();
        }
        return $result->getResult();
    }

    /**
     * @inheritDoc
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
            throw new CouldNotSaveException(__('The linked products data is invalid. Verify the data and try again.'));
        }
        return true;
    }

    /**
     * @inheritDoc
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
     * Get Link resource instance.
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Link
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
     * Get LinkTypeProvider instance.
     *
     * @return LinkTypeProvider
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
     * Get MetadataPool instance.
     *
     * @return \Magento\Framework\EntityManager\MetadataPool
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
