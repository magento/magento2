<?php

namespace Magento\Wishlist\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Wishlist\Api\Data\ItemInterface;
use Magento\Wishlist\Api\ItemRepositoryInterface;

class ItemRepository implements ItemRepositoryInterface
{
    /**
     * @var ResourceModel\Item
     */
    private $itemResource;
    /**
     * @var ItemFactory
     */
    private $itemFactory;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
    /**
     * @var \Magento\Wishlist\Api\Data\WishlistSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;
    /**
     * @var ResourceModel\Item\CollectionFactory
     */
    private $collectionFactory;

    /**
     * ItemRepository constructor.
     * @param ResourceModel\Item $itemResource
     * @param ItemFactory $itemFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param ResourceModel\Item\CollectionFactory $collectionFactory
     * @param \Magento\Wishlist\Api\Data\WishlistSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        ResourceModel\Item $itemResource,
        ItemFactory $itemFactory,
        CollectionProcessorInterface $collectionProcessor,
        \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory $collectionFactory,
        \Magento\Wishlist\Api\Data\WishlistSearchResultsInterfaceFactory $searchResultsFactory
    ) {

        $this->itemResource = $itemResource;
        $this->itemFactory = $itemFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id): \Magento\Wishlist\Api\Data\ItemInterface
    {
        $item = $this->itemFactory->create();
        $this->itemResource->load($item, $id);
        if (!$id) {
            throw new NoSuchEntityException(__('Wishlist item with id "%1" does not exist.', $id));
        }

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria): \Magento\Wishlist\Api\Data\ItemSearchResultsInterface
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToSelect('*');
        $this->collectionProcessor->process($searchCriteria, $collection);
        $collection->load();

        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Wishlist\Api\Data\ItemInterface $item): ItemInterface {

        $hasDataChanges = $item->hasDataChanges();
        $item->setIsOptionsSaved(false);

        try {
            $item = $this->itemResource->save($item);
        } catch (\Exception $e) {
            throw new StateException(__('Cannot save wishlist item'));
        }


        if ($hasDataChanges && !$item->isOptionsSaved()) {
            $item->saveItemOptions();
        }


        return $item->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($id) {
        $item = $this->get($id);

        try {
            $this->itemResource->delete($item);
        } catch (\Exception $e) {
            throw new StateException(__('Cannot delete wishlist item'));
        }

        return true;
    }
}
