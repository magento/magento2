<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model\StockItemConfiguration;

use Magento\InventoryConfiguration\Model\StockItemConfiguration;
use Magento\Framework\ObjectManager\NoninterceptableInterface;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;

/**
 * Class Proxy replaces StockItemConfigurationInterface object with StockItemInterface object
 */
class Proxy extends StockItemConfiguration implements NoninterceptableInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * @var string
     */
    private $instanceName = null;

    /**
     * @var \Magento\InventoryConfiguration\Model\StockItemConfiguration
     */
    private $subject = null;

    /**
     * @var bool
     */
    private $isShared = null;

    /**
     * @var bool
     */
    private $itemFactory;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @param bool $shared
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\CatalogInventory\Model\Stock\ItemFactory $itemFactory,
        $instanceName = '\\Magento\\CatalogInventory\\Model\\Stock\\Item',
        $shared = true
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
        $this->isShared = $shared;
        $this->itemFactory = $itemFactory;
    }

    /**
     * @return array|string[]
     */
    public function __sleep()
    {
        return ['subject', 'isShared', 'instanceName'];
    }
    
    /**
     *
     */
    public function __wakeup()
    {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     *
     */
    public function __clone()
    {
        $this->subject = clone $this->getSubject();
    }

    /**
     * @return \Magento\InventoryConfiguration\Model\StockItemConfiguration
     */
    private function getSubject()
    {
        if (!$this->subject) {
            $this->subject = true === $this->isShared
                ? $this->objectManager->get($this->instanceName)
                : $this->objectManager->create($this->instanceName);
        }
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->getSubject()->getSku();
    }

    /**
     * @param string $sku
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setSku(string $sku): StockItemConfigurationInterface
    {
        return $this->getSubject()->setSku($sku);
    }

    /**
     * @return int
     */
    public function getStockId(): int
    {
        return $this->getSubject()->getStockId();
    }

    /**
     * @param int $stockId
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setStockId(int $stockId): StockItemConfigurationInterface
    {
        return $this->getSubject()->setStockId($stockId);
    }

    /**
     * @return float
     */
    public function getQty(): float
    {
        return $this->getSubject()->getQty();
    }

    /**
     * @param float $qty
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setQty(float $qty): StockItemConfigurationInterface
    {
        return $this->getSubject()->setQty($qty);
    }

    /**
     * @return bool
     */
    public function getIsQtyDecimal(): bool
    {
        return $this->getSubject()->getIsQtyDecimal();
    }

    /**
     * @param bool $isQtyDecimal
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setIsQtyDecimal(bool $isQtyDecimal): StockItemConfigurationInterface
    {
        return $this->getSubject()->setIsQtyDecimal($isQtyDecimal);
    }

    /**
     * @return bool
     */
    public function getShowDefaultNotificationMessage(): bool
    {
        return $this->getSubject()->getShowDefaultNotificationMessage();
    }

    /**
     * @param bool $showDefaultNotificationMessage
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setShowDefaultNotificationMessage(bool $showDefaultNotificationMessage): StockItemConfigurationInterface
    {
        return $this->getSubject()->setShowDefaultNotificationMessage($showDefaultNotificationMessage);
    }

    /**
     * @return bool
     */
    public function getUseConfigMinQty(): bool
    {
        return $this->getSubject()->getUseConfigMinQty();
    }

    /**
     * @param bool $useConfigMinQty
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setUseConfigMinQty(bool $useConfigMinQty): StockItemConfigurationInterface
    {
        return $this->getSubject()->setUseConfigMinQty($useConfigMinQty);
    }

    /**
     * @return float
     */
    public function getMinQty(): float
    {
        return $this->getSubject()->getMinQty();
    }

    /**
     * @param float $minQty
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setMinQty(float $minQty): StockItemConfigurationInterface
    {
        return $this->getSubject()->setMinQty($minQty);
    }

    /**
     * @return bool
     */
    public function getUseConfigMinSaleQty(): bool
    {
        return $this->getSubject()->getUseConfigMinSaleQty();
    }

    /**
     * @param bool $useConfigMinSaleQty
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setUseConfigMinSaleQty(bool $useConfigMinSaleQty): StockItemConfigurationInterface
    {
        return $this->getSubject()->setUseConfigMinSaleQty($useConfigMinSaleQty);
    }

    /**
     * @return float
     */
    public function getMinSaleQty(): float
    {
        return $this->getSubject()->getMinSaleQty();
    }

    /**
     * @param float $minSaleQty
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setMinSaleQty(float $minSaleQty): StockItemConfigurationInterface
    {
        return $this->getSubject()->setMinSaleQty($minSaleQty);
    }

    /**
     * @return bool
     */
    public function getUseConfigMaxSaleQty(): bool
    {
        return $this->getSubject()->getUseConfigMaxSaleQty();
    }

    /**
     * @param bool $useConfigMaxSaleQty
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setUseConfigMaxSaleQty(bool $useConfigMaxSaleQty): StockItemConfigurationInterface
    {
        return $this->getSubject()->setUseConfigMaxSaleQty($useConfigMaxSaleQty);
    }

    /**
     * @return float
     */
    public function getMaxSaleQty(): float
    {
        return $this->getSubject()->getMaxSaleQty();
    }

    /**
     * @param float $maxSaleQty
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setMaxSaleQty(float $maxSaleQty): StockItemConfigurationInterface
    {
        return $this->getSubject()->setMaxSaleQty($maxSaleQty);
    }

    /**
     * @return bool
     */
    public function getUseConfigBackorders(): bool
    {
        return $this->getSubject()->getUseConfigBackorders();
    }

    /**
     * @param bool $useConfigBackorders
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setUseConfigBackorders(bool $useConfigBackorders): StockItemConfigurationInterface
    {
        return $this->getSubject()->setUseConfigBackorders($useConfigBackorders);
    }

    /**
     * @return int
     */
    public function getBackorders(): int
    {
        return $this->getSubject()->getBackorders();
    }

    /**
     * @param int $backOrders
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setBackorders(int $backOrders): StockItemConfigurationInterface
    {
        return $this->getSubject()->setBackorders($backOrders);
    }

    /**
     * @return bool
     */
    public function getUseConfigNotifyStockQty(): bool
    {
        return $this->getSubject()->getUseConfigNotifyStockQty();
    }

    /**
     * @param bool $useConfigNotifyStockQty
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setUseConfigNotifyStockQty(bool $useConfigNotifyStockQty): StockItemConfigurationInterface
    {
        return $this->getSubject()->setUseConfigNotifyStockQty($useConfigNotifyStockQty);
    }

    /**
     * @return float
     */
    public function getNotifyStockQty(): float
    {
        return $this->getSubject()->getNotifyStockQty();
    }

    /**
     * @param float $notifyStockQty
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setNotifyStockQty(float $notifyStockQty): StockItemConfigurationInterface
    {
        return $this->getSubject()->setNotifyStockQty($notifyStockQty);
    }

    /**
     * @return bool
     */
    public function getUseConfigQtyIncrements(): bool
    {
        return $this->getSubject()->getUseConfigQtyIncrements();
    }

    /**
     * @param bool $useConfigQtyIncrements
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setUseConfigQtyIncrements(bool $useConfigQtyIncrements): StockItemConfigurationInterface
    {
        return $this->getSubject()->setUseConfigQtyIncrements($useConfigQtyIncrements);
    }

    /**
     * @return float
     */
    public function getQtyIncrements(): float
    {
        return $this->getSubject()->getQtyIncrements();
    }

    /**
     * @param float $qtyIncrements
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setQtyIncrements(float $qtyIncrements): StockItemConfigurationInterface
    {
        return $this->getSubject()->setQtyIncrements($qtyIncrements);
    }

    /**
     * @return bool
     */
    public function getUseConfigEnableQtyInc(): bool
    {
        return $this->getSubject()->getUseConfigEnableQtyInc();
    }

    /**
     * @param bool $useConfigEnableQtyInc
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setUseConfigEnableQtyInc(bool $useConfigEnableQtyInc): StockItemConfigurationInterface
    {
        return $this->getSubject()->setUseConfigEnableQtyInc($useConfigEnableQtyInc);
    }

    /**
     * @return bool
     */
    public function getEnableQtyIncrements(): bool
    {
        return $this->getSubject()->getEnableQtyIncrements();
    }

    /**
     * @param bool $enableQtyIncrements
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setEnableQtyIncrements(bool $enableQtyIncrements): StockItemConfigurationInterface
    {
        return $this->getSubject()->setEnableQtyIncrements($enableQtyIncrements);
    }

    /**
     * @return bool
     */
    public function getUseConfigManageStock(): bool
    {
        return $this->getSubject()->getUseConfigManageStock();
    }

    /**
     * @param bool $useConfigManageStock
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setUseConfigManageStock(bool $useConfigManageStock): StockItemConfigurationInterface
    {
        return $this->getSubject()->setUseConfigManageStock($useConfigManageStock);
    }

    /**
     * @return bool
     */
    public function getManageStock(): bool
    {
        return $this->getSubject()->getManageStock();
    }

    /**
     * @param bool $manageStock
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setManageStock(bool $manageStock): StockItemConfigurationInterface
    {
        return $this->getSubject()->setManageStock($manageStock);
    }

    /**
     * @return bool
     */
    public function getIsInStock(): bool
    {
        return $this->getSubject()->getIsInStock();
    }

    /**
     * @param bool $isInStock
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setIsInStock(bool $isInStock): StockItemConfigurationInterface
    {
        return $this->getSubject()->setIsInStock($isInStock);
    }

    /**
     * @return string
     */
    public function getLowStockDate(): string
    {
        return $this->getSubject()->getLowStockDate();
    }

    /**
     * @param string $lowStockDate
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setLowStockDate(string $lowStockDate): StockItemConfigurationInterface
    {
        return $this->getSubject()->setLowStockDate($lowStockDate);
    }

    /**
     * @return bool
     */
    public function getIsDecimalDivided(): bool
    {
        return $this->getSubject()->getIsDecimalDivided();
    }

    /**
     * @param bool $isDecimalDivided
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setIsDecimalDivided(bool $isDecimalDivided): StockItemConfigurationInterface
    {
        return $this->getSubject()->setIsDecimalDivided($isDecimalDivided);
    }

    /**
     * @return int
     */
    public function getStockStatusChangedAuto(): int
    {
        return $this->getSubject()->getStockStatusChangedAuto();
    }

    /**
     * @param int $stockStatusChangedAuto
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setStockStatusChangedAuto(int $stockStatusChangedAuto): StockItemConfigurationInterface
    {
        return $this->getSubject()->setStockStatusChangedAuto($stockStatusChangedAuto);
    }

    /**
     * @return \Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationExtensionInterface|null
     */
    public function getExtensionAttributes(): \Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationExtensionInterface
    {
        return $this->getSubject()->getExtensionAttributes();
    }

    /**
     * @param \Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationExtensionInterface $extensionAttributes
     * @return $this|\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface
     */
    public function setExtensionAttributes(\Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationExtensionInterface $extensionAttributes): StockItemConfigurationInterface
    {
        return $this->getSubject()->setExtensionAttributes($extensionAttributes);
    }

    /**
     * @return \Magento\Framework\Api\AttributeInterface[]|null
     */
    public function getCustomAttributes(): array
    {
        return $this->getSubject()->getCustomAttributes();
    }

    /**
     * @param string $attributeCode
     * @return \Magento\Framework\Api\AttributeInterface|null
     */
    public function getCustomAttribute($attributeCode)
    {
        return $this->getSubject()->getCustomAttribute($attributeCode);
    }

    /**
     * @param array $attributes
     * @return $this|\Magento\Framework\Model\AbstractExtensibleModel
     */
    public function setCustomAttributes(array $attributes)
    {
        return $this->getSubject()->setCustomAttributes($attributes);
    }

    /**
     * @param $attributeCode
     * @param $attributeValue
     * @return $this
     */
    public function setCustomAttribute($attributeCode, $attributeValue)
    {
        return $this->getSubject()->setCustomAttribute($attributeCode, $attributeValue);
    }

    /**
     * @param $key
     * @param null $value
     * @return $this
     */
    public function setData($key, $value = null)
    {
        return $this->getSubject()->setData($key, $value);
    }

    /**
     * @param null $key
     * @return $this
     */
    public function unsetData($key = null)
    {
        return $this->getSubject()->unsetData($key);
    }

    /**
     * @param string $key
     * @param null $index
     * @return mixed
     */
    public function getData($key = '', $index = null)
    {
        return $this->getSubject()->getData($key, $index);
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setId($value)
    {
        return $this->getSubject()->setId($value);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setIdFieldName($name)
    {
        return $this->getSubject()->setIdFieldName($name);
    }

    /**
     * @return string
     */
    public function getIdFieldName()
    {
        return $this->getSubject()->getIdFieldName();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->getSubject()->getId();
    }

    /**
     * @param null $isDeleted
     * @return bool
     */
    public function isDeleted($isDeleted = null)
    {
        return $this->getSubject()->isDeleted($isDeleted);
    }

    /**
     * @return bool
     */
    public function hasDataChanges()
    {
        return $this->getSubject()->hasDataChanges();
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setDataChanges($value)
    {
        return $this->getSubject()->setDataChanges($value);
    }

    /**
     * @param null $key
     * @return mixed
     */
    public function getOrigData($key = null)
    {
        return $this->getSubject()->getOrigData($key);
    }

    /**
     * @param null $key
     * @param null $data
     * @return $this
     */
    public function setOrigData($key = null, $data = null)
    {
        return $this->getSubject()->setOrigData($key, $data);
    }

    /**
     * @param string $field
     * @return bool
     */
    public function dataHasChangedFor($field)
    {
        return $this->getSubject()->dataHasChangedFor($field);
    }

    /**
     * @return string
     */
    public function getResourceName()
    {
        return $this->getSubject()->getResourceName();
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getResourceCollection()
    {
        return $this->getSubject()->getResourceCollection();
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getCollection()
    {
        return $this->getSubject()->getCollection();
    }

    /**
     * @param int $modelId
     * @param null $field
     * @return $this
     */
    public function load($modelId, $field = null)
    {
        return $this->getSubject()->load($modelId, $field);
    }

    /**
     * @param string $identifier
     * @param null $field
     */
    public function beforeLoad($identifier, $field = null)
    {
        return $this->getSubject()->beforeLoad($identifier, $field);
    }

    /**
     * @return $this
     */
    public function afterLoad()
    {
        return $this->getSubject()->afterLoad();
    }

    /**
     * @return bool
     */
    public function isSaveAllowed()
    {
        return $this->getSubject()->isSaveAllowed();
    }

    /**
     * @param bool $flag
     */
    public function setHasDataChanges($flag)
    {
        return $this->getSubject()->setHasDataChanges($flag);
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function save()
    {
        return $this->getSubject()->save();
    }

    /**
     * @return $this
     */
    public function afterCommitCallback()
    {
        return $this->getSubject()->afterCommitCallback();
    }

    /**
     * @param null $flag
     * @return bool
     */
    public function isObjectNew($flag = null)
    {
        return $this->getSubject()->isObjectNew($flag);
    }

    /**
     * @return $this
     */
    public function beforeSave()
    {
        return $this->getSubject()->beforeSave();
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Validator\Exception
     */
    public function validateBeforeSave()
    {
        return $this->getSubject()->validateBeforeSave();
    }

    /**
     * @return array|false
     */
    public function getCacheTags()
    {
        return $this->getSubject()->getCacheTags();
    }

    /**
     * @return $this
     */
    public function cleanModelCache()
    {
        return $this->getSubject()->cleanModelCache();
    }

    /**
     * @return $this
     */
    public function afterSave()
    {
        return $this->getSubject()->afterSave();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function delete()
    {
        return $this->getSubject()->delete();
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeDelete()
    {
        return $this->getSubject()->beforeDelete();
    }

    /**
     * @return $this
     */
    public function afterDelete()
    {
        return $this->getSubject()->afterDelete();
    }

    /**
     * @return $this
     */
    public function afterDeleteCommit()
    {
        return $this->getSubject()->afterDeleteCommit();
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    public function getResource()
    {
        return $this->getSubject()->getResource();
    }

    /**
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->getSubject()->getEntityId();
    }

    /**
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId)
    {
        return $this->getSubject()->setEntityId($entityId);
    }

    /**
     * @return $this
     */
    public function clearInstance()
    {
        return $this->getSubject()->clearInstance();
    }

    /**
     * @return array
     */
    public function getStoredData()
    {
        return $this->getSubject()->getStoredData();
    }

    /**
     * @return string
     */
    public function getEventPrefix()
    {
        return $this->getSubject()->getEventPrefix();
    }

    /**
     * @param array $arr
     * @return $this
     */
    public function addData(array $arr)
    {
        return $this->getSubject()->addData($arr);
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function getDataByPath($path)
    {
        return $this->getSubject()->getDataByPath($path);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getDataByKey($key)
    {
        return $this->getSubject()->getDataByKey($key);
    }

    /**
     * @param string $key
     * @param array $args
     * @return $this
     */
    public function setDataUsingMethod($key, $args = array())
    {
        return $this->getSubject()->setDataUsingMethod($key, $args);
    }

    /**
     * @param string $key
     * @param null $args
     * @return mixed
     */
    public function getDataUsingMethod($key, $args = null)
    {
        return $this->getSubject()->getDataUsingMethod($key, $args);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasData($key = '')
    {
        return $this->getSubject()->hasData($key);
    }

    /**
     * @param array $keys
     * @return array
     */
    public function toArray(array $keys = array())
    {
        return $this->getSubject()->toArray($keys);
    }

    /**
     * @param array $keys
     * @return array
     */
    public function convertToArray(array $keys = array())
    {
        return $this->getSubject()->convertToArray($keys);
    }

    /**
     * @param array $keys
     * @param string $rootName
     * @param bool $addOpenTag
     * @param bool $addCdata
     * @return string
     */
    public function toXml(array $keys = array(), $rootName = 'item', $addOpenTag = false, $addCdata = true)
    {
        return $this->getSubject()->toXml($keys, $rootName, $addOpenTag, $addCdata);
    }

    /**
     * @param array $arrAttributes
     * @param string $rootName
     * @param bool $addOpenTag
     * @param bool $addCdata
     * @return string
     */
    public function convertToXml(array $arrAttributes = array(), $rootName = 'item', $addOpenTag = false, $addCdata = true)
    {
        return $this->getSubject()->convertToXml($arrAttributes, $rootName, $addOpenTag, $addCdata);
    }

    /**
     * @param array $keys
     * @return bool|string
     */
    public function toJson(array $keys = array())
    {
        return $this->getSubject()->toJson($keys);
    }

    /**
     * @param array $keys
     * @return bool|string
     */
    public function convertToJson(array $keys = array())
    {
        return $this->getSubject()->convertToJson($keys);
    }

    /**
     * @param string $format
     * @return string
     */
    public function toString($format = '')
    {
        return $this->getSubject()->toString($format);
    }

    /**
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __call($method, $args)
    {
        return $this->getSubject()->__call($method, $args);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->getSubject()->isEmpty();
    }

    /**
     * @param array $keys
     * @param string $valueSeparator
     * @param string $fieldSeparator
     * @param string $quote
     * @return string
     */
    public function serialize($keys = array(), $valueSeparator = '=', $fieldSeparator = ' ', $quote = '"')
    {
        return $this->getSubject()->serialize($keys, $valueSeparator, $fieldSeparator, $quote);
    }

    /**
     * @param null $data
     * @param array $objects
     * @return array
     */
    public function debug($data = null, &$objects = array())
    {
        return $this->getSubject()->debug($data, $objects);
    }

    /**
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        return $this->getSubject()->offsetSet($offset, $value);
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->getSubject()->offsetExists($offset);
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        return $this->getSubject()->offsetUnset($offset);
    }

    /**
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getSubject()->offsetGet($offset);
    }
}
