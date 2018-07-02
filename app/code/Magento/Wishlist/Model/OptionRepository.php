<?php

namespace Magento\Wishlist\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Wishlist\Api\Data\OptionInterface;
use Magento\Wishlist\Api\OptionRepositoryInterface;
use Magento\Wishlist\Model\Item\OptionFactory;
use Magento\Wishlist\Model\ResourceModel\Item\Option;

class OptionRepository implements OptionRepositoryInterface
{
    /**
     * @var Option
     */
    private $optionResource;
    /**
     * @var OptionFactory
     */
    private $optionFactory;
    /**
     * @var Option\CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
    /**
     * @var \Magento\Wishlist\Api\Data\OptionSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * OptionRepository constructor.
     * @param Option $optionResource
     * @param OptionFactory $optionFactory
     * @param Option\CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param \Magento\Wishlist\Api\Data\OptionSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        Option $optionResource,
        OptionFactory $optionFactory,
        Option\CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        \Magento\Wishlist\Api\Data\OptionSearchResultsInterfaceFactory $searchResultsFactory
    ) {

        $this->optionResource = $optionResource;
        $this->optionFactory = $optionFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function get($code): \Magento\Wishlist\Api\Data\OptionInterface
    {
        $option = $this->optionFactory->create();
        $this->optionResource->load($option, $code, OptionInterface::CODE);
        if (!$option->getId()) {
            throw new NoSuchEntityException(__('Option with code "%1" does not exist.', $code));
        }
        return $option;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id): \Magento\Wishlist\Api\Data\OptionInterface
    {
        $option = $this->optionFactory->create();
        $this->optionResource->load($option, $id);
        if (!$option->getId()) {
            throw new NoSuchEntityException(__('Option with id "%1" does not exist.', $id));
        }
        return $option;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ): \Magento\Wishlist\Api\Data\OptionSearchResultsInterface {
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
    public function save(\Magento\Wishlist\Api\Data\OptionInterface $option
    ): \Magento\Wishlist\Api\Data\OptionInterface {
        try {
            $option = $this->optionResource->save($option);
        } catch (\Exception $e) {
            throw new StateException(__('Cannot save option'));
        }

        return $option->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($id)
    {
        $option = $this->getById($id);
        try {
            $this->optionResource->delete($option);
        } catch (\Exception $e) {
            throw new StateException(__('Cannot delete option'));
        }
        return true;
    }
}
