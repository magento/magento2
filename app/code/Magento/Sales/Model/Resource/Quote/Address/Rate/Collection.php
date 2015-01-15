<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Quote\Address\Rate;

/**
 * Quote addresses shipping rates collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Whether to load fixed items only
     *
     * @var bool
     */
    protected $_allowFixedOnly = false;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Sales\Model\Quote\Address\CarrierFactoryInterface $carrierFactory
     * @param \Zend_Db_Adapter_Abstract $connection
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Sales\Model\Quote\Address\CarrierFactoryInterface $carrierFactory,
        $connection = null,
        \Magento\Framework\Model\Resource\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_carrierFactory = $carrierFactory;
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Quote\Address\Rate', 'Magento\Sales\Model\Resource\Quote\Address\Rate');
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
     * @param \Magento\Sales\Model\Quote\Address\Rate $rate
     * @return $this
     */
    public function addItem(\Magento\Framework\Object $rate)
    {
        $carrier = $this->_carrierFactory->get($rate->getCarrier());
        if ($this->_allowFixedOnly && (!$carrier || !$carrier->isFixed())) {
            return $this;
        }
        return parent::addItem($rate);
    }
}
