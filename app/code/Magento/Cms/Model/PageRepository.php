<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Model;

use Magento\Cms\Api\Data;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\Data\PageInterfaceFactory;
use Magento\Cms\Api\Data\PageSearchResultsInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\Api\SearchCriteria\PageCollectionProcessor;
use Magento\Cms\Model\Page\IdentityMap;
use Magento\Cms\Model\ResourceModel\Page as ResourcePage;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\HydratorInterface;
use Magento\Framework\App\Route\Config;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Cms page repository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PageRepository implements PageRepositoryInterface
{
    /**
     * @var ResourcePage
     */
    protected $resource;

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var PageCollectionFactory
     */
    protected $pageCollectionFactory;

    /**
     * @var Data\PageSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var PageInterfaceFactory
     */
    protected $dataPageFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var IdentityMap
     */
    private $identityMap;

    /**
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * @var Config
     */
    private $routeConfig;

    /**
     * @param ResourcePage $resource
     * @param PageFactory $pageFactory
     * @param PageInterfaceFactory $dataPageFactory
     * @param PageCollectionFactory $pageCollectionFactory
     * @param Data\PageSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param IdentityMap|null $identityMap
     * @param HydratorInterface|null $hydrator
     * @param Config|null $routeConfig
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ResourcePage $resource,
        PageFactory $pageFactory,
        PageInterfaceFactory $dataPageFactory,
        PageCollectionFactory $pageCollectionFactory,
        Data\PageSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor = null,
        ?IdentityMap $identityMap = null,
        ?HydratorInterface $hydrator = null,
        ?Config $routeConfig = null
    ) {
        $this->resource = $resource;
        $this->pageFactory = $pageFactory;
        $this->pageCollectionFactory = $pageCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataPageFactory = $dataPageFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
        $this->identityMap = $identityMap ?? ObjectManager::getInstance()
                ->get(IdentityMap::class);
        $this->hydrator = $hydrator ?: ObjectManager::getInstance()
            ->get(HydratorInterface::class);
        $this->routeConfig = $routeConfig ?? ObjectManager::getInstance()
                ->get(Config::class);
    }

    /**
     * Validate new layout update values.
     *
     * @param PageInterface $page
     * @return void
     * @throws \InvalidArgumentException
     */
    private function validateLayoutUpdate(PageInterface $page): void
    {
        //Persisted data
        $oldData = null;
        if ($page->getId() && $page instanceof Page) {
            $oldData = $page->getOrigData();
        }
        //Custom layout update can be removed or kept as is.
        if ($page->getCustomLayoutUpdateXml()
            && (
                !$oldData
                || $page->getCustomLayoutUpdateXml() !== $oldData[Data\PageInterface::CUSTOM_LAYOUT_UPDATE_XML]
            )
        ) {
            throw new \InvalidArgumentException('Custom layout updates must be selected from a file');
        }
        if ($page->getLayoutUpdateXml()
            && (!$oldData || $page->getLayoutUpdateXml() !== $oldData[Data\PageInterface::LAYOUT_UPDATE_XML])
        ) {
            throw new \InvalidArgumentException('Custom layout updates must be selected from a file');
        }
    }

    /**
     * Save Page data
     *
     * @param PageInterface|Page $page
     * @return Page
     * @throws CouldNotSaveException
     */
    public function save(PageInterface $page)
    {
        try {
            $pageId = $page->getId();
            if ($pageId && !($page instanceof Page && $page->getOrigData())) {
                $page = $this->hydrator->hydrate($this->getById($pageId), $this->hydrator->extract($page));
            }
            if ($page->getStoreId() === null) {
                $storeId = $this->storeManager->getStore()->getId();
                $page->setStoreId($storeId);
            }
            $this->validateLayoutUpdate($page);
            $this->validateRoutesDuplication($page);
            $this->resource->save($page);
            $this->identityMap->add($page);
        } catch (LocalizedException $exception) {
            throw new CouldNotSaveException(
                __('Could not save the page: %1', $exception->getMessage()),
                $exception
            );
        } catch (\Throwable $exception) {
            throw new CouldNotSaveException(
                __('Could not save the page: %1', __('Something went wrong while saving the page.')),
                $exception
            );
        }
        return $page;
    }

    /**
     * Load Page data by given Page Identity
     *
     * @param string $pageId
     * @return Page
     * @throws NoSuchEntityException
     */
    public function getById($pageId)
    {
        $page = $this->pageFactory->create();
        $page->load($pageId);
        if (!$page->getId()) {
            throw new NoSuchEntityException(__('The CMS page with the "%1" ID doesn\'t exist.', $pageId));
        }
        $this->identityMap->add($page);

        return $page;
    }

    /**
     * Load Page data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param SearchCriteriaInterface $criteria
     * @return PageSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $collection = $this->pageCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Delete Page
     *
     * @param PageInterface $page
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(PageInterface $page)
    {
        try {
            $this->resource->delete($page);
            $this->identityMap->remove($page->getId());
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the page: %1', $exception->getMessage())
            );
        }
        return true;
    }

    /**
     * Delete Page by given Page Identity
     *
     * @param string $pageId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($pageId)
    {
        return $this->delete($this->getById($pageId));
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated 102.0.0
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            // phpstan:ignore "Class Magento\Cms\Model\Api\SearchCriteria\PageCollectionProcessor not found."
            $this->collectionProcessor = ObjectManager::getInstance()
                ->get(PageCollectionProcessor::class);
        }
        return $this->collectionProcessor;
    }

    /**
     * Checks that page identifier doesn't duplicate existed routes
     *
     * @param PageInterface $page
     * @return void
     * @throws CouldNotSaveException
     */
    private function validateRoutesDuplication($page): void
    {
        if ($this->routeConfig->getRouteByFrontName($page->getIdentifier(), 'frontend')) {
            throw new CouldNotSaveException(
                __('The value specified in the URL Key field would generate a URL that already exists.')
            );
        }
    }
}
