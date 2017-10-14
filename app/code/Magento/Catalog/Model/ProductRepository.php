<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Gallery\MimeTypeExtensionMap;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Api\Data\ImageContentInterfaceFactory;
use Magento\Framework\Api\ImageContentValidatorInterface;
use Magento\Framework\Api\ImageProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\DB\Adapter\DeadlockException;
use Magento\Framework\DB\Adapter\LockWaitException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ProductRepository implements \Magento\Catalog\Api\ProductRepositoryInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductCustomOptionRepositoryInterface
     */
    protected $optionRepository;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Product[]
     */
    protected $instances = [];

    /**
     * @var Product[]
     */
    protected $instancesById = [];

    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper
     */
    protected $initializationHelper;

    /**
     * @var \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $resourceModel;

    /**
     * @var Product\Initialization\Helper\ProductLinks
     */
    protected $linkInitializer;

    /**
     * @var Product\LinkTypeProvider
     */
    protected $linkTypeProvider;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $metadataService;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @var ImageContentInterfaceFactory
     */
    protected $contentFactory;

    /**
     * @var ImageProcessorInterface
     */
    protected $imageProcessor;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var \Magento\Catalog\Model\Product\Gallery\Processor
     */
    protected $mediaGalleryProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var int
     */
    private $cacheLimit = 0;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * ProductRepository constructor.
     * @param ProductFactory $productFactory
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $initializationHelper
     * @param \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory
     * @param ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param ResourceModel\Product $resourceModel
     * @param Product\Initialization\Helper\ProductLinks $linkInitializer
     * @param Product\LinkTypeProvider $linkTypeProvider
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataServiceInterface
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param Product\Option\Converter $optionConverter
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param ImageContentValidatorInterface $contentValidator
     * @param ImageContentInterfaceFactory $contentFactory
     * @param MimeTypeExtensionMap $mimeTypeExtensionMap
     * @param ImageProcessorInterface $imageProcessor
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionProcessorInterface $collectionProcessor [optional]
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param int $cacheLimit [optional]
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ProductFactory $productFactory,
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $initializationHelper,
        \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Catalog\Model\ResourceModel\Product $resourceModel,
        \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks $linkInitializer,
        \Magento\Catalog\Model\Product\LinkTypeProvider $linkTypeProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataServiceInterface,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        \Magento\Catalog\Model\Product\Option\Converter $optionConverter,
        \Magento\Framework\Filesystem $fileSystem,
        ImageContentValidatorInterface $contentValidator,
        ImageContentInterfaceFactory $contentFactory,
        MimeTypeExtensionMap $mimeTypeExtensionMap,
        ImageProcessorInterface $imageProcessor,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        CollectionProcessorInterface $collectionProcessor = null,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        $cacheLimit = 1000
    ) {
        $this->productFactory = $productFactory;
        $this->collectionFactory = $collectionFactory;
        $this->initializationHelper = $initializationHelper;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resourceModel = $resourceModel;
        $this->linkInitializer = $linkInitializer;
        $this->linkTypeProvider = $linkTypeProvider;
        $this->storeManager = $storeManager;
        $this->attributeRepository = $attributeRepository;
        $this->filterBuilder = $filterBuilder;
        $this->metadataService = $metadataServiceInterface;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->fileSystem = $fileSystem;
        $this->contentFactory = $contentFactory;
        $this->imageProcessor = $imageProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->cacheLimit = (int)$cacheLimit;
    }

    /**
     * {@inheritdoc}
     */
    public function get($sku, $editMode = false, $storeId = null, $forceReload = false)
    {
        $cacheKey = $this->getCacheKey([$editMode, $storeId]);
        if (!isset($this->instances[$sku][$cacheKey]) || $forceReload) {
            $product = $this->productFactory->create();

            $productId = $this->resourceModel->getIdBySku($sku);
            if (!$productId) {
                throw new NoSuchEntityException(__('Requested product doesn\'t exist'));
            }
            if ($editMode) {
                $product->setData('_edit_mode', true);
            }
            if ($storeId !== null) {
                $product->setData('store_id', $storeId);
            }
            $product->load($productId);
            $this->cacheProduct($cacheKey, $product);
        }
        if (!isset($this->instances[$sku])) {
            $sku = trim($sku);
        }
        return $this->instances[$sku][$cacheKey];
    }

    /**
     * {@inheritdoc}
     */
    public function getById($productId, $editMode = false, $storeId = null, $forceReload = false)
    {
        $cacheKey = $this->getCacheKey([$editMode, $storeId]);
        if (!isset($this->instancesById[$productId][$cacheKey]) || $forceReload) {
            $product = $this->productFactory->create();
            if ($editMode) {
                $product->setData('_edit_mode', true);
            }
            if ($storeId !== null) {
                $product->setData('store_id', $storeId);
            }
            $product->load($productId);
            if (!$product->getId()) {
                throw new NoSuchEntityException(__('Requested product doesn\'t exist'));
            }
            $this->cacheProduct($cacheKey, $product);
        }
        return $this->instancesById[$productId][$cacheKey];
    }

    /**
     * Get key for cache
     *
     * @param array $data
     * @return string
     */
    protected function getCacheKey($data)
    {
        $serializeData = [];
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $serializeData[$key] = $value->getId();
            } else {
                $serializeData[$key] = $value;
            }
        }
        $serializeData = $this->serializer->serialize($serializeData);
        return sha1($serializeData);
    }

    /**
     * Add product to internal cache and truncate cache if it has more than cacheLimit elements.
     *
     * @param string $cacheKey
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return void
     */
    private function cacheProduct($cacheKey, \Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $this->instancesById[$product->getId()][$cacheKey] = $product;
        $this->instances[$product->getSku()][$cacheKey] = $product;

        if ($this->cacheLimit && count($this->instances) > $this->cacheLimit) {
            $offset = round($this->cacheLimit / -2);
            $this->instancesById = array_slice($this->instancesById, $offset, null, true);
            $this->instances = array_slice($this->instances, $offset);
        }
    }

    /**
     * Merge data from DB and updates from request
     *
     * @param array $productData
     * @param bool $createNew
     * @return \Magento\Catalog\Api\Data\ProductInterface|Product
     * @throws NoSuchEntityException
     */
    protected function initializeProductData(array $productData, $createNew)
    {
        unset($productData['media_gallery']);
        if ($createNew) {
            $product = $this->productFactory->create();
            if ($this->storeManager->hasSingleStore()) {
                $product->setWebsiteIds([$this->storeManager->getStore(true)->getWebsiteId()]);
            }
        } else {
            unset($this->instances[$productData['sku']]);
            $product = $this->get($productData['sku']);
        }

        foreach ($productData as $key => $value) {
            $product->setData($key, $value);
        }
        $this->assignProductToWebsites($product, $createNew);

        return $product;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $createNew
     * @return void
     */
    private function assignProductToWebsites(\Magento\Catalog\Model\Product $product, $createNew)
    {
        $websiteIds = $product->getWebsiteIds();

        if (!$this->storeManager->hasSingleStore()) {
            $websiteIds = array_unique(
                array_merge(
                    $websiteIds,
                    [$this->storeManager->getStore()->getWebsiteId()]
                )
            );
        }

        if ($createNew && $this->storeManager->getStore(true)->getCode() == \Magento\Store\Model\Store::ADMIN_CODE) {
            $websiteIds = array_keys($this->storeManager->getWebsites());
        }

        $product->setWebsiteIds($websiteIds);
    }

    /**
     * @param ProductInterface $product
     * @param array $newEntry
     * @return $this
     * @throws InputException
     * @throws StateException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function processNewMediaGalleryEntry(
        ProductInterface $product,
        array  $newEntry
    ) {
        /** @var ImageContentInterface $contentDataObject */
        $contentDataObject = $newEntry['content'];

        /** @var \Magento\Catalog\Model\Product\Media\Config $mediaConfig */
        $mediaConfig = $product->getMediaConfig();
        $mediaTmpPath = $mediaConfig->getBaseTmpMediaPath();

        $relativeFilePath = $this->imageProcessor->processImageContent($mediaTmpPath, $contentDataObject);
        $tmpFilePath = $mediaConfig->getTmpMediaShortUrl($relativeFilePath);

        if (!$product->hasGalleryAttribute()) {
            throw new StateException(__('Requested product does not support images.'));
        }

        $imageFileUri = $this->getMediaGalleryProcessor()->addImage(
            $product,
            $tmpFilePath,
            isset($newEntry['types']) ? $newEntry['types'] : [],
            true,
            isset($newEntry['disabled']) ? $newEntry['disabled'] : true
        );
        // Update additional fields that are still empty after addImage call
        $this->getMediaGalleryProcessor()->updateImage(
            $product,
            $imageFileUri,
            [
                'label' => $newEntry['label'],
                'position' => $newEntry['position'],
                'disabled' => $newEntry['disabled'],
                'media_type' => $newEntry['media_type'],
            ]
        );
        return $this;
    }

    /**
     * Process product links, creating new links, updating and deleting existing links
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Magento\Catalog\Api\Data\ProductLinkInterface[] $newLinks
     * @return $this
     * @throws NoSuchEntityException
     */
    private function processLinks(\Magento\Catalog\Api\Data\ProductInterface $product, $newLinks)
    {
        if ($newLinks === null) {
            // If product links were not specified, don't do anything
            return $this;
        }

        // Clear all existing product links and then set the ones we want
        $linkTypes = $this->linkTypeProvider->getLinkTypes();
        foreach (array_keys($linkTypes) as $typeName) {
            $this->linkInitializer->initializeLinks($product, [$typeName => []]);
        }

        // Set each linktype info
        if (!empty($newLinks)) {
            $productLinks = [];
            foreach ($newLinks as $link) {
                $productLinks[$link->getLinkType()][] = $link;
            }

            foreach ($productLinks as $type => $linksByType) {
                $assignedSkuList = [];
                /** @var \Magento\Catalog\Api\Data\ProductLinkInterface $link */
                foreach ($linksByType as $link) {
                    $assignedSkuList[] = $link->getLinkedProductSku();
                }
                $linkedProductIds = $this->resourceModel->getProductsIdsBySkus($assignedSkuList);

                $linksToInitialize = [];
                foreach ($linksByType as $link) {
                    $linkDataArray = $this->extensibleDataObjectConverter
                        ->toNestedArray($link, [], \Magento\Catalog\Api\Data\ProductLinkInterface::class);
                    $linkedSku = $link->getLinkedProductSku();
                    if (!isset($linkedProductIds[$linkedSku])) {
                        throw new NoSuchEntityException(
                            __('Product with SKU "%1" does not exist', $linkedSku)
                        );
                    }
                    $linkDataArray['product_id'] = $linkedProductIds[$linkedSku];
                    $linksToInitialize[$linkedProductIds[$linkedSku]] = $linkDataArray;
                }

                $this->linkInitializer->initializeLinks($product, [$type => $linksToInitialize]);
            }
        }

        $product->setProductLinks($newLinks);
        return $this;
    }

    /**
     * Process Media gallery data before save product.
     *
     * Compare Media Gallery Entries Data with existing Media Gallery
     * * If Media entry has not value_id set it as new
     * * If Existing entry 'value_id' absent in Media Gallery set 'removed' flag
     * * Merge Existing and new media gallery
     *
     * @param ProductInterface $product contains only existing media gallery items
     * @param array $mediaGalleryEntries array which contains all media gallery items
     * @return $this
     * @throws InputException
     * @throws StateException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function processMediaGallery(ProductInterface $product, $mediaGalleryEntries)
    {
        $existingMediaGallery = $product->getMediaGallery('images');
        $newEntries = [];
        $entriesById = [];
        if (!empty($existingMediaGallery)) {
            foreach ($mediaGalleryEntries as $entry) {
                if (isset($entry['value_id'])) {
                    $entriesById[$entry['value_id']] = $entry;
                } else {
                    $newEntries[] = $entry;
                }
            }
            foreach ($existingMediaGallery as $key => &$existingEntry) {
                if (isset($entriesById[$existingEntry['value_id']])) {
                    $updatedEntry = $entriesById[$existingEntry['value_id']];
                    if ($updatedEntry['file'] === null) {
                        unset($updatedEntry['file']);
                    }
                    $existingMediaGallery[$key] = array_merge($existingEntry, $updatedEntry);
                } else {
                    //set the removed flag
                    $existingEntry['removed'] = true;
                }
            }
            $product->setData('media_gallery', ["images" => $existingMediaGallery]);
        } else {
            $newEntries = $mediaGalleryEntries;
        }

        $this->getMediaGalleryProcessor()->clearMediaAttribute($product, array_keys($product->getMediaAttributes()));
        $images = $product->getMediaGallery('images');
        if ($images) {
            foreach ($images as $image) {
                if (!isset($image['removed']) && !empty($image['types'])) {
                    $this->getMediaGalleryProcessor()->setMediaAttribute($product, $image['types'], $image['file']);
                }
            }
        }

        foreach ($newEntries as $newEntry) {
            if (!isset($newEntry['content'])) {
                throw new InputException(__('The image content is not valid.'));
            }
            /** @var ImageContentInterface $contentDataObject */
            $contentDataObject = $this->contentFactory->create()
                ->setName($newEntry['content']['data'][ImageContentInterface::NAME])
                ->setBase64EncodedData($newEntry['content']['data'][ImageContentInterface::BASE64_ENCODED_DATA])
                ->setType($newEntry['content']['data'][ImageContentInterface::TYPE]);
            $newEntry['content'] = $contentDataObject;
            $this->processNewMediaGalleryEntry($product, $newEntry);

            $finalGallery = $product->getData('media_gallery');
            $newEntryId = key(array_diff_key($product->getData('media_gallery')['images'], $entriesById));
            $newEntry = array_replace_recursive($newEntry, $finalGallery['images'][$newEntryId]);
            $entriesById[$newEntryId] = $newEntry;
            $finalGallery['images'][$newEntryId] = $newEntry;
            $product->setData('media_gallery', $finalGallery);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save(\Magento\Catalog\Api\Data\ProductInterface $product, $saveOptions = false)
    {
        $tierPrices = $product->getData('tier_price');

        try {
            $existingProduct = $this->get($product->getSku());

            $product->setData(
                $this->resourceModel->getLinkField(),
                $existingProduct->getData($this->resourceModel->getLinkField())
            );
            if (!$product->hasData(Product::STATUS)) {
                $product->setStatus($existingProduct->getStatus());
            }
        } catch (NoSuchEntityException $e) {
            $existingProduct = null;
        }

        $productDataArray = $this->extensibleDataObjectConverter
            ->toNestedArray($product, [], \Magento\Catalog\Api\Data\ProductInterface::class);
        $productDataArray = array_replace($productDataArray, $product->getData());
        $ignoreLinksFlag = $product->getData('ignore_links_flag');
        $productLinks = null;
        if (!$ignoreLinksFlag && $ignoreLinksFlag !== null) {
            $productLinks = $product->getProductLinks();
        }
        $productDataArray['store_id'] = (int)$this->storeManager->getStore()->getId();
        $product = $this->initializeProductData($productDataArray, empty($existingProduct));

        $this->processLinks($product, $productLinks);
        if (isset($productDataArray['media_gallery'])) {
            $this->processMediaGallery($product, $productDataArray['media_gallery']['images']);
        }

        if (!$product->getOptionsReadonly()) {
            $product->setCanSaveCustomOptions(true);
        }

        $validationResult = $this->resourceModel->validate($product);
        if (true !== $validationResult) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Invalid product data: %1', implode(',', $validationResult))
            );
        }

        try {
            if ($tierPrices !== null) {
                $product->setData('tier_price', $tierPrices);
            }
            unset($this->instances[$product->getSku()]);
            unset($this->instancesById[$product->getId()]);
            $this->resourceModel->save($product);
        } catch (ConnectionException $exception) {
            throw new \Magento\Framework\Exception\TemporaryState\CouldNotSaveException(
                __('Database connection error'),
                $exception,
                $exception->getCode()
            );
        } catch (DeadlockException $exception) {
            throw new \Magento\Framework\Exception\TemporaryState\CouldNotSaveException(
                __('Database deadlock found when trying to get lock'),
                $exception,
                $exception->getCode()
            );
        } catch (LockWaitException $exception) {
            throw new \Magento\Framework\Exception\TemporaryState\CouldNotSaveException(
                __('Database lock wait timeout exceeded'),
                $exception,
                $exception->getCode()
            );
        } catch (\Magento\Eav\Model\Entity\Attribute\Exception $exception) {
            throw \Magento\Framework\Exception\InputException::invalidFieldValue(
                $exception->getAttributeCode(),
                $product->getData($exception->getAttributeCode()),
                $exception
            );
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (LocalizedException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__('Unable to save product'), $e);
        }
        unset($this->instances[$product->getSku()]);
        unset($this->instancesById[$product->getId()]);
        return $this->get($product->getSku(), false, $product->getStoreId());
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $sku = $product->getSku();
        $productId = $product->getId();
        try {
            unset($this->instances[$product->getSku()]);
            unset($this->instancesById[$product->getId()]);
            $this->resourceModel->delete($product);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove product %1', $sku)
            );
        }
        unset($this->instances[$sku]);
        unset($this->instancesById[$productId]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($sku)
    {
        $product = $this->get($sku);
        return $this->delete($product);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection);

        $collection->addAttributeToSelect('*');
        $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');

        $this->collectionProcessor->process($searchCriteria, $collection);

        $collection->load();

        $collection->addCategoryIds();
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        foreach ($collection->getItems() as $product) {
            $this->cacheProduct(
                $this->getCacheKey(
                    [
                        false,
                        $product->hasData(\Magento\Catalog\Model\Product::STORE_ID) ? $product->getStoreId() : null
                    ]
                ),
                $product
            );
        }

        return $searchResult;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @deprecated 101.1.0
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        Collection $collection
    ) {
        $fields = [];
        $categoryFilter = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $conditionType = $filter->getConditionType() ? $filter->getConditionType() : 'eq';

            if ($filter->getField() == 'category_id') {
                $categoryFilter[$conditionType][] = $filter->getValue();
                continue;
            }
            $fields[] = ['attribute' => $filter->getField(), $conditionType => $filter->getValue()];
        }

        if ($categoryFilter) {
            $collection->addCategoriesFilter($categoryFilter);
        }

        if ($fields) {
            $collection->addFieldToFilter($fields);
        }
    }

    /**
     * Clean internal product cache
     *
     * @return void
     */
    public function cleanCache()
    {
        $this->instances = null;
        $this->instancesById = null;
    }

    /**
     * @return Product\Gallery\Processor
     */
    private function getMediaGalleryProcessor()
    {
        if (null === $this->mediaGalleryProcessor) {
            $this->mediaGalleryProcessor = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Catalog\Model\Product\Gallery\Processor::class);
        }
        return $this->mediaGalleryProcessor;
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated 101.1.0
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\Catalog\Model\Api\SearchCriteria\ProductCollectionProcessor'
            );
        }
        return $this->collectionProcessor;
    }
}
