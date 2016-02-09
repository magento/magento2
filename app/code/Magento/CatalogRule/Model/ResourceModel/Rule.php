<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog rules resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogRule\Model\ResourceModel;

use Magento\Catalog\Model\Product;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Rule extends \Magento\Rule\Model\ResourceModel\AbstractResource
{
    /**
     * Store number of seconds in a day
     */
    const SECONDS_IN_DAY = 86400;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Catalog rule data
     *
     * @var \Magento\CatalogRule\Helper\Data
     */
    protected $_catalogRuleData = null;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_coreDate;

    /**
     * @var \Magento\Catalog\Model\Product\ConditionFactory
     */
    protected $_conditionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Framework\Model\EntityManager
     */
    protected $entityManager;

    /**
     * Rule constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Product\ConditionFactory $conditionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\CatalogRule\Helper\Data $catalogRuleData
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Model\EntityManager $entityManager
     * @param array $associatedEntitiesMap
     * @param null $connectionName
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\ConditionFactory $conditionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\CatalogRule\Helper\Data $catalogRuleData,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Model\EntityManager $entityManager,
        array $associatedEntitiesMap = [],
        $connectionName = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_conditionFactory = $conditionFactory;
        $this->_coreDate = $coreDate;
        $this->_eavConfig = $eavConfig;
        $this->_eventManager = $eventManager;
        $this->_catalogRuleData = $catalogRuleData;
        $this->_logger = $logger;
        $this->dateTime = $dateTime;
        $this->priceCurrency = $priceCurrency;
        $this->entityManager = $entityManager;
        $this->_associatedEntitiesMap = $associatedEntitiesMap;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize main table and table id field
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init('catalogrule', 'rule_id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $rule
     * @return $this
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $rule)
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getTable('catalogrule_product'),
            ['rule_id=?' => $rule->getId()]
        );
        $connection->delete(
            $this->getTable('catalogrule_group_website'),
            ['rule_id=?' => $rule->getId()]
        );
        return parent::_afterDelete($rule);
    }

    /**
     * Get catalog rules product price for specific date, website and
     * customer group
     *
     * @param \DateTime $date
     * @param int $wId
     * @param int $gId
     * @param int $pId
     * @return float|false
     */
    public function getRulePrice($date, $wId, $gId, $pId)
    {
        $data = $this->getRulePrices($date, $wId, $gId, [$pId]);
        if (isset($data[$pId])) {
            return $data[$pId];
        }

        return false;
    }

    /**
     * Retrieve product prices by catalog rule for specific date, website and customer group
     * Collect data with  product Id => price pairs
     *
     * @param \DateTime $date
     * @param int $websiteId
     * @param int $customerGroupId
     * @param array $productIds
     * @return array
     */
    public function getRulePrices(\DateTime $date, $websiteId, $customerGroupId, $productIds)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('catalogrule_product_price'), ['product_id', 'rule_price'])
            ->where('rule_date = ?', $date->format('Y-m-d'))
            ->where('website_id = ?', $websiteId)
            ->where('customer_group_id = ?', $customerGroupId)
            ->where('product_id IN(?)', $productIds);

        return $connection->fetchPairs($select);
    }

    /**
     * Get active rule data based on few filters
     *
     * @param int|string $date
     * @param int $websiteId
     * @param int $customerGroupId
     * @param int $productId
     * @return array
     */
    public function getRulesFromProduct($date, $websiteId, $customerGroupId, $productId)
    {
        $connection = $this->getConnection();
        if (is_string($date)) {
            $date = strtotime($date);
        }
        $select = $connection->select()
            ->from($this->getTable('catalogrule_product'))
            ->where('website_id = ?', $websiteId)
            ->where('customer_group_id = ?', $customerGroupId)
            ->where('product_id = ?', $productId)
            ->where('from_time = 0 or from_time < ?', $date)
            ->where('to_time = 0 or to_time > ?', $date);

        return $connection->fetchAll($select);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        $this->entityManager->load('Magento\CatalogRule\Api\Data\RuleInterface', $object, $value);

        $this->unserializeFields($object);
        $this->_afterLoad($object);

        return $this;
    }

    /**
     * @param AbstractModel $object
     * @return $this
     * @throws \Exception
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->isDeleted()) {
            return $this->delete($object);
        }

        $this->beginTransaction();

        try {
            if (!$this->isModified($object)) {
                $this->processNotModifiedSave($object);
                $this->commit();
                $object->setHasDataChanges(false);
                return $this;
            }
            $object->validateBeforeSave();
            $object->beforeSave();
            if ($object->isSaveAllowed()) {
                $this->_serializeFields($object);
                $this->_beforeSave($object);
                $this->_checkUnique($object);
                $this->objectRelationProcessor->validateDataIntegrity($this->getMainTable(), $object->getData());

                $this->entityManager->save(
                    'Magento\CatalogRule\Api\Data\RuleInterface',
                    $object
                );

                $this->unserializeFields($object);
                $this->processAfterSaves($object);
            }
            $this->addCommitCallback([$object, 'afterCommitCallback'])->commit();
            $object->setHasDataChanges(false);
        } catch (\Exception $e) {
            $this->rollBack();
            $object->setHasDataChanges(true);
            throw $e;
        }
        return $this;
    }

    /**
     * @param AbstractModel $object
     * @return $this
     * @throws \Exception
     */
    public function delete(\Magento\Framework\Model\AbstractModel $object)
    {
        //TODO: add object relation processor support
        $this->transactionManager->start($this->getConnection());
        try {
            $object->beforeDelete();
            $this->_beforeDelete($object);
            $this->entityManager->delete(
                'Magento\CatalogRule\Api\Data\RuleInterface',
                $object
            );
            $this->_afterDelete($object);
            $object->isDeleted(true);
            $object->afterDelete();
            $this->transactionManager->commit();
            $object->afterDeleteCommit();
        } catch (\Exception $e) {
            $this->transactionManager->rollBack();
            throw $e;
        }

        return $this;
    }
}
