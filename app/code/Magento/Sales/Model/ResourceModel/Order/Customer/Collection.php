<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Customer;

use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\EntityFactory as EavEntityFactory;
use Magento\Eav\Model\ResourceModel\Helper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Copy\Config;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Customer Grid Collection
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\Customer\Model\ResourceModel\Customer\Collection
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor
     *
     * @param EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param EavConfig $eavConfig
     * @param ResourceConnection $resource
     * @param EavEntityFactory $eavEntityFactory
     * @param Helper $resourceHelper
     * @param UniversalFactory $universalFactory
     * @param Snapshot $entitySnapshot
     * @param Config $fieldsetConfig
     * @param StoreManagerInterface $storeManager
     * @param AdapterInterface|null $connection
     * @param string $modelName
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        EavConfig $eavConfig,
        ResourceConnection $resource,
        EavEntityFactory $eavEntityFactory,
        Helper $resourceHelper,
        UniversalFactory $universalFactory,
        Snapshot $entitySnapshot,
        Config $fieldsetConfig,
        StoreManagerInterface $storeManager,
        AdapterInterface $connection = null,
        $modelName = self::CUSTOMER_MODEL_NAME
    ) {
        $this->storeManager = $storeManager;
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
            $fieldsetConfig,
            $connection,
            $modelName
        );
    }

    /**
     * Init select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addNameToSelect()->addAttributeToSelect(
            'email'
        )->addAttributeToSelect(
            'created_at'
        )->joinAttribute(
            'billing_postcode',
            'customer_address/postcode',
            'default_billing',
            null,
            'left'
        )->joinAttribute(
            'billing_city',
            'customer_address/city',
            'default_billing',
            null,
            'left'
        )->joinAttribute(
            'billing_telephone',
            'customer_address/telephone',
            'default_billing',
            null,
            'left'
        )->joinAttribute(
            'billing_regione',
            'customer_address/region',
            'default_billing',
            null,
            'left'
        )->joinAttribute(
            'billing_country_id',
            'customer_address/country_id',
            'default_billing',
            null,
            'left'
        );
        return $this;
    }

    /**
     * Performance issue fix for large number (over million) of customers on Orders Creation page in Admin
     *
     * Initially _initSelect method of this collection had two joins to Store and Website
     * tables on store_id and website_id to add store and website names. This caused extreme performance drop
     * resulting in loading page over minute, despite all optimizations were already in place.
     * To fix the issue the joins were removed, instead store and website names are added in this method.
     *
     * @param DataObject $item
     * @return DataObject
     */
    protected function beforeAddLoadedItem(DataObject $item): DataObject
    {
        $storeId = $item->getStoreId();
        $storeName = $storeId !== null ? $this->storeManager->getStore($storeId)->getName() : null;
        $item->setStoreName($storeName);

        $websiteId = $item->getWebsiteId();
        $websiteName = $websiteId !== null ? $this->storeManager->getWebsite($websiteId)->getName() : null;
        $item->setWebsiteName($websiteName);

        return parent::beforeAddLoadedItem($item);
    }

    /**
     * @inheritDoc
     *
     * @throws LocalizedException
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store_name') {
            $this->joinField(
                'store_name',
                'store',
                'name',
                'store_id=store_id',
                null,
                'left'
            );
        }

        if ($field === 'website_name') {
            $this->joinField(
                'website_name',
                'store_website',
                'name',
                'website_id=website_id',
                null,
                'left'
            );
        }

        return parent::addFieldToFilter($field, $condition);
    }
}
