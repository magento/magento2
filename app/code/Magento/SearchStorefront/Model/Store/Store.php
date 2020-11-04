<?php

namespace Magento\SearchStorefront\Model\Store;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreExtensionInterface;

/**
 * Store model stub for search service
 */
class Store extends \Magento\Framework\DataObject implements \Magento\Store\Api\Data\StoreInterface, \Magento\Framework\App\ScopeInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     * @param array              $data
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        array $data = []
    ) {
        parent::__construct($data);
        $this->resourceConnection = $resourceConnection;
    }

    public function getId()
    {
        return (int)$this->getData('id');
    }

    public function setId($id)
    {
        $this->setData('id', (int)$id);
        return $this;
    }

    public function getCode()
    {
        return (string)$this->getData('code');
    }

    public function setCode($code)
    {
        $this->setData('code', (string)$code);
        return $this;
    }

    public function getName()
    {
        return (string)$this->getData('name');
    }

    public function setName($name)
    {
        $this->setData('name', (string)$name);
        return $this;
    }

    public function getWebsiteId()
    {
        return (int)$this->getData('website_id');
    }

    public function setWebsiteId($websiteId)
    {
        $this->setData('website_id', (int)$websiteId);
        return $this;
    }

    public function getStoreGroupId()
    {
        return (int)$this->getData('store_group_id');
    }

    public function setIsActive($isActive)
    {
        $this->setData('is_active', (int)$isActive);
        return $this;
    }

    public function getIsActive()
    {
        return (int)$this->getData('is_active');
    }

    public function setStoreGroupId($storeGroupId)
    {
        $this->setData('store_group_id', (int)$storeGroupId);
        return $this;
    }

    public function getExtensionAttributes()
    {
        return $this->getData('extension_attributes');
    }

    public function setExtensionAttributes(StoreExtensionInterface $extensionAttributes)
    {
        $this->setData('extension_attributes', $extensionAttributes);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getScopeType()
    {
        return 'store';
    }

    /**
     * @inheritDoc
     */
    public function getScopeTypeName()
    {
        return 'search-service';
    }

    /**
     * Get root category id
     *
     * @return mixed
     */
    public function getRootCategoryId()
    {
        return $this->_getData('root_category_id');
    }

    /**
     * Set root category id
     * @param $rootCategoryId
     * @return Store
     */
    public function setRootCategoryId($rootCategoryId)
    {
        return $this->setData('root_category_id', $rootCategoryId);
    }

    /**
     * Solution for temp passing customerGroupId to price filter
     * @param int $customerGroupId
     * @return $this|Store
     */
    public function setCustomerGroupId($customerGroupId)
    {
        $this->setData('customer_group_id', (int)$customerGroupId);
        return $this;
    }

    public function getCustomerGroupId()
    {
        return (int)$this->getData('customer_group_id');
    }

    /**
     * Stub method to load data with raw sql.
     *
     * @return $this
     * @throws NoSuchEntityException
     */
    public function load()
    {
        $storeId = $this->getData('id') ?? \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->from(
                ['store' => $this->resourceConnection->getTableName('store')],
                [
                    'website_id' => 'website_id',
                    'code'       => 'code',
                    'name'       => 'name',
                    'group_id'   => 'group_id'
                ]
            )
            ->join(
                ['store_group' => $this->resourceConnection->getTableName('store_group')],
                'store.group_id = store_group.group_id',
                ['root_category_id' => 'store_group.root_category_id']
            )
            ->where('store.store_id = ?', $storeId);

        $result = $connection->fetchRow($select);

        if (!$result) {
            throw new NoSuchEntityException(__('No such store id=%1 available', $storeId));
        }

        $this->setWebsiteId($result['website_id']);
        $this->setRootCategoryId($result['root_category_id']);
        $this->setCode($result['code']);
        $this->setName($result['name']);
        $this->setStoreGroupId($result['group_id']);

        return $this;
    }
}
