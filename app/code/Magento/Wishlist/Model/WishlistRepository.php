<?php

namespace Magento\Wishlist\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Wishlist\Api\Data\WishlistInterface;
use Magento\Wishlist\Api\Data\WishlistSearchResultsInterface;
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
     * WishlistRepository constructor.
     * @param CollectionProcessorInterface $collectionProcessor
     * @param WishlistFactory $wishlistFactory
     * @param \Magento\Wishlist\Api\Data\WishlistSearchResultsInterfaceFactory $searchResultsFactory
     * @param ResourceModel\Wishlist\CollectionFactory $collectionFactory
     * @param ResourceModel\Wishlist $wishlistResource
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        WishlistFactory $wishlistFactory,
        \Magento\Wishlist\Api\Data\WishlistSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Wishlist\Model\ResourceModel\Wishlist\CollectionFactory $collectionFactory,
        \Magento\Wishlist\Model\ResourceModel\Wishlist $wishlistResource
    ) {

        $this->wishlistResource = $wishlistResource;
        $this->wishlistFactory = $wishlistFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
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
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
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
        try {
            $this->wishlistResource->save($wishlist);
        } catch (\Exception $e) {
            throw new StateException(__('Cannot save wishlist'));
        }
        return $wishlist;

    }

    /**
     * @inheritdoc
     */
    public function delete(WishlistInterface $wishlist)
    {
        try {
            $this->wishlistResource->delete($wishlist);
        } catch (\Exception $e) {
            throw new StateException(__('Cannot delete wishlist.'));
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        $wishlist = $this->get($id);
        $this->delete($wishlist);
        return true;
    }
}
