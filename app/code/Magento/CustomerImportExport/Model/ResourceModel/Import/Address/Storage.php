<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerImportExport\Model\ResourceModel\Import\Address;

use Magento\Customer\Model\ResourceModel\Address\CollectionFactory as AddressCollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\ImportExport\Model\Import\AbstractEntity;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIterator as CollectionIterator;
use Magento\Customer\Model\ResourceModel\Address\Collection as AddressCollection;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Storage to check existing addresses.
 */
class Storage
{
    /**
     * IDs of addresses grouped by customer IDs.
     *
     * @var int[][]
     */
    private $addresses = [];

    /**
     * @var AddressCollectionFactory
     */
    private $addressCollectionFactory;

    /**
     * For iterating over large number of addresses.
     *
     * @var CollectionIterator
     */
    private $collectionIterator;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @param AddressCollectionFactory $addressCollectionFactory
     * @param CollectionIterator $byPagesIterator
     * @param ScopeConfigInterface $config
     */
    public function __construct(
        AddressCollectionFactory $addressCollectionFactory,
        CollectionIterator $byPagesIterator,
        ScopeConfigInterface $config
    ) {
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->collectionIterator = $byPagesIterator;
        $this->config = $config;
    }

    /**
     * Record existing address.
     *
     * @param int $customerId
     * @param int $addressId
     * @return void
     */
    private function addRecord(int $customerId, int $addressId): void
    {
        if (!$customerId || !$addressId) {
            return;
        }

        if (!array_key_exists($customerId, $this->addresses)) {
            $this->addresses[$customerId] = [];
        }

        if (!in_array($addressId, $this->addresses[$customerId], true)) {
            $this->addresses[$customerId][] = $addressId;
        }
    }

    /**
     * Load addresses IDs for given customers.
     *
     * @param int[] $customerIds
     *
     * @return void
     */
    private function loadAddresses(array $customerIds): void
    {
        /** @var AddressCollection $collection */
        $collection = $this->addressCollectionFactory->create();
        $collection->removeAttributeToSelect();
        $select = $collection->getSelect();
        $tableId = array_keys($select->getPart(Select::FROM))[0];
        $select->reset(Select::COLUMNS)->columns([$tableId . '.entity_id', $tableId . '.parent_id']);

        $pageSize = $this->config->getValue(AbstractEntity::XML_PATH_PAGE_SIZE);
        $pageSize = $pageSize !== null ? (int) $pageSize : null;
        $getChuck = function (int $offset) use ($customerIds, $pageSize) {
            return array_slice($customerIds, $offset, $pageSize);
        };
        $offset = 0;
        for ($idsChunk = $getChuck($offset); !empty($idsChunk); $offset += $pageSize, $idsChunk = $getChuck($offset)) {
            $chunkSelect = clone $select;
            $chunkSelect->where($tableId .'.parent_id IN (?)', $idsChunk);
            $addresses = $collection->getConnection()->fetchAll($chunkSelect);
            foreach ($addresses as $address) {
                $this->addRecord((int) $address['parent_id'], (int) $address['entity_id']);
            }
        }
    }

    /**
     * Check if given address exists for given customer.
     *
     * @param int $addressId
     * @param int $forCustomerId
     * @return bool
     */
    public function doesExist(int $addressId, int $forCustomerId): bool
    {
        return array_key_exists($forCustomerId, $this->addresses)
            && in_array(
                $addressId,
                $this->addresses[$forCustomerId],
                true
            );
    }

    /**
     * Pre-load addresses for given customers.
     *
     * @param int[] $forCustomersIds
     * @return void
     */
    public function prepareAddresses(array $forCustomersIds): void
    {
        if (!$forCustomersIds) {
            return;
        }

        $forCustomersIds = array_unique($forCustomersIds);
        $customerIdsToUse = array_diff($forCustomersIds, array_keys($this->addresses));
        $this->loadAddresses($customerIdsToUse);
    }
}
