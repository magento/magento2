<?php

namespace Magento\Wishlist\Model;

use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Wishlist\Api\Data\WishlistInterface;
use Magento\Wishlist\Api\WishlistRepositoryInterface;

class WishlistRepository implements WishlistRepositoryInterface
{
    /**
     * @var ResourceModel\Wishlist
     */
    private $wishlistResource;
    /**
     * @var WishlistFactory
     */
    private $wishlistFactory;
    /**
     * @var ResourceModel\Wishlist\CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
    /**
     * @var \Magento\Wishlist\Api\Data\WishlistSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;
    /**
     * @var ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * WishlistRepository constructor.
     * @param CollectionProcessorInterface $collectionProcessor
     * @param WishlistFactory $wishlistFactory
     * @param \Magento\Wishlist\Api\Data\WishlistSearchResultsInterfaceFactory $searchResultsFactory
     * @param ResourceModel\Wishlist\CollectionFactory $collectionFactory
     * @param ResourceModel\Wishlist $wishlistResource
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        WishlistFactory $wishlistFactory,
        \Magento\Wishlist\Api\Data\WishlistSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Wishlist\Model\ResourceModel\Wishlist\CollectionFactory $collectionFactory,
        \Magento\Wishlist\Model\ResourceModel\Wishlist $wishlistResource,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {

        $this->wishlistResource = $wishlistResource;
        $this->wishlistFactory = $wishlistFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * @inheritdoc
     */
    public function get($sharingCode): WishlistInterface
    {
        $wishlist = $this->wishlistFactory->create();
        $this->wishlistResource->load($wishlist, $sharingCode, WishlistInterface::SHARING_CODE);
        if (!$wishlist->getId()) {
            throw new NoSuchEntityException(__('Wishlist with sharing code "%1" does not exist.', $sharingCode));
        }
        return $wishlist->getDataModel();
    }

    /**
     * @inheritdoc
     */
    public function getById($id): WishlistInterface
    {
        $wishlist = $this->wishlistFactory->create();
        $this->wishlistResource->load($wishlist, $id);
        if (!$id) {
            throw new NoSuchEntityException(__('Wishlist with id "%1" does not exist.', $id));
        }

        return $wishlist->getDataModel();
    }

    /**
     * @inheritdoc
     */
    public function getByCustomer(\Magento\Customer\Api\Data\CustomerInterface $customer
    ): \Magento\Wishlist\Api\Data\WishlistInterface {
        $wishlist = $this->wishlistFactory->create();
        $this->wishlistResource->load($wishlist, $customer->getId(), WishlistInterface::CUSTOMER_ID);

        if (!$wishlist->getId()) {
            throw new NoSuchEntityException(__('Wishlist for customer: "%1" does not exist.', $customer->getId()));
        }

        return $wishlist->getDataModel();

    }

    /**
     * @inheritdoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria): \Magento\Wishlist\Api\Data\WishlistSearchResultsInterface
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToSelect('*');
        $this->collectionProcessor->process($searchCriteria, $collection);
        $collection->load();

        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setTotalCount($collection->getSize());

        foreach ($collection as $wishlistModel) {
            $customers[] = $wishlistModel->getDataModel();
        }
        $searchResult->setItems($customers);

        return $searchResult;
    }

    /**
     * @inheritdoc
     */
    public function save(WishlistInterface $wishlist): WishlistInterface
    {
        $wishlistData = $this->extensibleDataObjectConverter->toNestedArray(
            $wishlist,
            [],
            \Magento\Wishlist\Api\Data\WishlistInterface::class
        );
        /** @var Customer $customerModel */
        $wishlistModel = $this->wishlistFactory->create(
            ['data' => $wishlistData]
        );
        //Model's actual ID field maybe different than "id"
        //so "id" field from $customerData may be ignored.
        $wishlistModel->setId($wishlist->getId());

        try {
            $this->wishlistResource->save($wishlistModel);
        } catch (\Exception $e) {
            throw new StateException(__('Cannot save wishlist'));
        }
        return $wishlist->getId();

    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        $wishlist = $this->get($id);
        try {
            $this->wishlistResource->delete($wishlist);
        } catch (\Exception $e) {
            throw new StateException(__('Cannot delete wishlist.'));
        }
        return true;

    }
}
