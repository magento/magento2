<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Customer\Model\ResourceModel\Form\Attribute\Collection as AttributeCollection;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\ResourceModel\Metadata;
use Magento\Sales\Api\Data\OrderAddressSearchResultInterfaceFactory as SearchResultFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;

/**
 * Repository class for @see \Magento\Sales\Api\Data\OrderAddressInterface
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressRepository implements \Magento\Sales\Api\OrderAddressRepositoryInterface
{
    /**
     * @var Metadata
     */
    protected $metadata;

    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory = null;

    /**
     * @var \Magento\Sales\Api\Data\OrderAddressInterface[]
     */
    protected $registry = [];

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var AttributeMetadataDataProvider
     */
    private $attributeMetadataDataProvider;

    /**
     * @var AttributeCollection|null
     */
    private $attributesList = null;

    /**
     * AddressRepository constructor.
     * @param Metadata $metadata
     * @param SearchResultFactory $searchResultFactory
     * @param CollectionProcessorInterface|null $collectionProcessor
     * @param AttributeMetadataDataProvider $attributeMetadataDataProvider
     */
    public function __construct(
        Metadata $metadata,
        SearchResultFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor = null,
        AttributeMetadataDataProvider $attributeMetadataDataProvider = null
    ) {
        $this->metadata = $metadata;
        $this->searchResultFactory = $searchResultFactory;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider ?: ObjectManager::getInstance()
            ->get(AttributeMetadataDataProvider::class);
    }

    /**
     * Format multiline and multiselect attributes
     *
     * @param OrderAddressInterface $orderAddress
     *
     * @return void
     */
    private function formatCustomAddressAttributes(OrderAddressInterface $orderAddress)
    {
        $attributesList = $this->getAttributesList();

        foreach ($attributesList as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if (!$orderAddress->hasData($attributeCode)) {
                continue;
            }
            $attributeValue = $orderAddress->getData($attributeCode);
            if (is_array($attributeValue)) {
                $glue = $attribute->getFrontendInput() === 'multiline' ? PHP_EOL : ',';
                $attributeValue = trim(implode($glue, $attributeValue));
            }
            $orderAddress->setData($attributeCode, $attributeValue);
        }
    }

    /**
     * Get list of custom attributes.
     *
     * @return AttributeCollection|null
     */
    private function getAttributesList()
    {
        if (!$this->attributesList) {
            $attributesList = $this->attributeMetadataDataProvider->loadAttributesCollection(
                'customer_address',
                'customer_register_address'
            );
            $attributesList->addFieldToFilter('is_user_defined', 1);
            $attributesList->addFieldToFilter(
                'frontend_input',
                [
                    'in' => [
                        'multiline',
                        'multiselect',
                    ],
                ]
            );

            $this->attributesList = $attributesList;
        }

        return $this->attributesList;
    }

    /**
     * Loads a specified order address.
     *
     * @param int $id
     * @return \Magento\Sales\Api\Data\OrderAddressInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id)
    {
        if (!$id) {
            throw new InputException(__('Id required'));
        }

        if (!isset($this->registry[$id])) {
            /** @var \Magento\Sales\Api\Data\OrderAddressInterface $entity */
            $entity = $this->metadata->getNewInstance()->load($id);
            if (!$entity->getEntityId()) {
                throw new NoSuchEntityException(__('Requested entity doesn\'t exist'));
            }

            $this->registry[$id] = $entity;
        }

        return $this->registry[$id];
    }

    /**
     * Find order addresses by criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Sales\Api\Data\OrderAddressInterface[]
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Address\Collection $searchResult */
        $searchResult = $this->searchResultFactory->create();
        $this->collectionProcessor->process($searchCriteria, $searchResult);
        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult;
    }

    /**
     * Deletes a specified order address.
     *
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Magento\Sales\Api\Data\OrderAddressInterface $entity)
    {
        try {
            $this->metadata->getMapper()->delete($entity);

            unset($this->registry[$entity->getEntityId()]);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete order address'), $e);
        }

        return true;
    }

    /**
     * Deletes order address by Id.
     *
     * @param int $id
     * @return bool
     */
    public function deleteById($id)
    {
        $entity = $this->get($id);

        return $this->delete($entity);
    }

    /**
     * Performs persist operations for a specified order address.
     *
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $entity
     * @return \Magento\Sales\Api\Data\OrderAddressInterface
     * @throws CouldNotSaveException
     */
    public function save(\Magento\Sales\Api\Data\OrderAddressInterface $entity)
    {
        $this->formatCustomAddressAttributes($entity);
        try {
            $this->metadata->getMapper()->save($entity);
            $this->registry[$entity->getEntityId()] = $entity;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save order address'), $e);
        }

        return $this->registry[$entity->getEntityId()];
    }

    /**
     * Creates new order address instance.
     *
     * @return \Magento\Sales\Api\Data\OrderAddressInterface
     */
    public function create()
    {
        return $this->metadata->getNewInstance();
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated 100.2.0
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
            );
        }
        return $this->collectionProcessor;
    }
}
