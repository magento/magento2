<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\ViewModel\Customer\Address;

use Magento\Customer\Model\ResourceModel\Address\Collection;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Customer's addresses filter as per allowed country filter for corresponding store
 */
class AddressAttributeFilter extends \Magento\Eav\Model\Entity\Collection\VersionControl\AbstractCollection implements ArgumentInterface
{
    /**
     * @var AllowedCountries
     */
    private $allowedCountryReader;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Eav\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot
     * @param AllowedCountries $allowedCountryReader
     * @param Collection $collection
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        AllowedCountries $allowedCountryReader,
        Collection $collection,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        $this->allowedCountryReader = $allowedCountryReader;
        $this->collection = $collection;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $entitySnapshot,
            $connection
        );
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Customer\Model\Address::class, \Magento\Customer\Model\ResourceModel\Address::class);
    }

    /**
     * Set allowed country filter for customer's addresses
     *
     * @return $this|Object
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setScopeFilter($storeId)
    {
        if ($storeId) {
            $allowedCountries = $this->allowedCountryReader->getAllowedCountries(
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $this->collection->addAttributeToFilter('country_id', ['in' => $allowedCountries]);
        }

        return $this->collection;
    }
}
