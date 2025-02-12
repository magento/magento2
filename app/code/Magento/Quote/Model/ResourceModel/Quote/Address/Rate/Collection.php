<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\ResourceModel\Quote\Address\Rate;

use Magento\Quote\Model\ResourceModel\Quote\Address\Rate;

/**
 * Quote addresses shipping rates collection
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\VersionControl\Collection
{
    /**
     * Whether to load fixed items only
     *
     * @var bool
     */
    protected $_allowFixedOnly = false;

    /**
     * @var \Magento\Shipping\Model\CarrierFactoryInterface
     */
    private $_carrierFactory;

    /**
     * @var Delete
     */
    private Delete $deleteRates;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot
     * @param \Magento\Shipping\Model\CarrierFactoryInterface $carrierFactory
     * @param Delete $deleteRates
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Shipping\Model\CarrierFactoryInterface $carrierFactory,
        Delete $deleteRates,
        ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        ?\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $connection,
            $resource
        );
        $this->deleteRates = $deleteRates;
        $this->_carrierFactory = $carrierFactory;
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Quote\Model\Quote\Address\Rate::class,
            \Magento\Quote\Model\ResourceModel\Quote\Address\Rate::class
        );
    }

    /**
     * Set filter by address id
     *
     * @param int $addressId
     * @return $this
     */
    public function setAddressFilter($addressId)
    {
        if ($addressId) {
            $this->addFieldToFilter('address_id', $addressId);
        } else {
            $this->_totalRecords = 0;
            $this->_setIsLoaded(true);
        }
        return $this;
    }

    /**
     * Setter for loading fixed items only
     *
     * @param bool $value
     * @return $this
     */
    public function setFixedOnlyFilter($value)
    {
        $this->_allowFixedOnly = (bool)$value;
        return $this;
    }

    /**
     * Don't add item to the collection if only fixed are allowed and its carrier is not fixed
     *
     * @param \Magento\Quote\Model\Quote\Address\Rate $rate
     * @return $this
     */
    public function addItem(\Magento\Framework\DataObject $rate)
    {
        $carrier = $this->_carrierFactory->get($rate->getCarrier());
        if ($this->_allowFixedOnly && (!$carrier || !$carrier->isFixed())) {
            return $this;
        }
        return parent::addItem($rate);
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        $itemsToDelete = [];
        $itemsToSave = [];
        /** @var Rate $item */
        foreach ($this->getItems() as $key => $item) {
            if ($item->isDeleted()) {
                $itemsToDelete[] = $item;
                $this->removeItemByKey($key);
            } else {
                $itemsToSave[] = $item;
            }
        }
        $this->deleteRates->execute($itemsToDelete);
        /** @var Rate $item */
        foreach ($itemsToSave as $item) {
            $item->save();
        }
        return $this;
    }
}
