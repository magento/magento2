<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\ResourceModel;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Rule\Model\ResourceModel\AbstractResource;
use Magento\SalesRule\Api\Data\RuleInterface;

/**
 * Sales Rule resource model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Rule extends AbstractResource
{
    /**
     * Store associated with rule entities information map
     *
     * @var array
     * @since 2.0.0
     */
    protected $_associatedEntitiesMap = [];

    /**
     * @var array
     * @since 2.0.0
     */
    protected $customerGroupIds = [];

    /**
     * @var array
     * @since 2.0.0
     */
    protected $websiteIds = [];

    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     * @since 2.0.0
     */
    protected $string;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon
     * @since 2.0.0
     */
    protected $_resourceCoupon;

    /**
     * @var EntityManager
     * @since 2.1.0
     */
    protected $entityManager;

    /**
     * @var MetadataPool
     * @since 2.2.0
     */
    private $metadataPool;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\SalesRule\Model\ResourceModel\Coupon $resourceCoupon
     * @param string $connectionName
     * @param \Magento\Framework\DataObject|null $associatedEntityMapInstance
     * @param Json $serializer Optional parameter for backward compatibility
     * @param MetadataPool $metadataPool Optional parameter for backward compatibility
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\SalesRule\Model\ResourceModel\Coupon $resourceCoupon,
        $connectionName = null,
        \Magento\Framework\DataObject $associatedEntityMapInstance = null,
        Json $serializer = null,
        MetadataPool $metadataPool = null
    ) {
        $this->string = $string;
        $this->_resourceCoupon = $resourceCoupon;
        $associatedEntitiesMapInstance = $associatedEntityMapInstance ?: ObjectManager::getInstance()->get(
            \Magento\SalesRule\Model\ResourceModel\Rule\AssociatedEntityMap::class
        );
        $this->_associatedEntitiesMap = $associatedEntitiesMapInstance->getData();
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->metadataPool = $metadataPool ?: ObjectManager::getInstance()->get(MetadataPool::class);
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize main table and table id field
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('salesrule', 'rule_id');
    }

    /**
     * @param AbstractModel $object
     * @return void
     * @deprecated 2.1.0
     * @since 2.0.0
     */
    public function loadCustomerGroupIds(AbstractModel $object)
    {
        if (!$this->customerGroupIds) {
            $this->customerGroupIds = (array)$this->getCustomerGroupIds($object->getId());
        }
        $object->setData('customer_group_ids', $this->customerGroupIds);
    }

    /**
     * @param AbstractModel $object
     * @return void
     * @deprecated 2.1.0
     * @since 2.0.0
     */
    public function loadWebsiteIds(AbstractModel $object)
    {
        if (!$this->websiteIds) {
            $this->websiteIds = (array)$this->getWebsiteIds($object->getId());
        }

        $object->setData('website_ids', $this->websiteIds);
    }

    /**
     * Prepare sales rule's discount quantity
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @since 2.0.0
     */
    public function _beforeSave(AbstractModel $object)
    {
        if (!$object->getDiscountQty()) {
            $object->setDiscountQty(new \Zend_Db_Expr('NULL'));
        }

        parent::_beforeSave($object);
        return $this;
    }

    /**
     * Load an object
     *
     * @param AbstractModel $object
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        $this->getEntityManager()->load($object, $value);
        return $this;
    }

    /**
     * Bind sales rule to customer group(s) and website(s).
     * Save rule's associated store labels.
     * Save product attributes used in rule.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @since 2.0.0
     */
    protected function _afterSave(AbstractModel $object)
    {
        if ($object->hasStoreLabels()) {
            $this->saveStoreLabels($object->getId(), $object->getStoreLabels());
        }

        // Save product attributes used in rule
        $ruleProductAttributes = array_merge(
            $this->getProductAttributes($this->serializer->serialize($object->getConditions()->asArray())),
            $this->getProductAttributes($this->serializer->serialize($object->getActions()->asArray()))
        );
        if (count($ruleProductAttributes)) {
            $this->setActualProductAttributes($object, $ruleProductAttributes);
        }

        // Update auto geterated specific coupons if exists
        if ($object->getUseAutoGeneration() && $object->hasDataChanges()) {
            $this->_resourceCoupon->updateSpecificCoupons($object);
        }
        return parent::_afterSave($object);
    }

    /**
     * Retrieve coupon/rule uses for specified customer
     *
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param int $customerId
     * @return string
     * @since 2.0.0
     */
    public function getCustomerUses($rule, $customerId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('salesrule_customer'),
            ['cnt' => 'count(*)']
        )->where(
            'rule_id = :rule_id'
        )->where(
            'customer_id = :customer_id'
        );
        return $connection->fetchOne($select, [':rule_id' => $rule->getRuleId(), ':customer_id' => $customerId]);
    }

    /**
     * Save rule labels for different store views
     *
     * @param int $ruleId
     * @param array $labels
     * @throws \Exception
     * @return $this
     * @since 2.0.0
     */
    public function saveStoreLabels($ruleId, $labels)
    {
        $deleteByStoreIds = [];
        $table = $this->getTable('salesrule_label');
        $connection = $this->getConnection();

        $data = [];
        foreach ($labels as $storeId => $label) {
            if ($this->string->strlen($label)) {
                $data[] = ['rule_id' => $ruleId, 'store_id' => $storeId, 'label' => $label];
            } else {
                $deleteByStoreIds[] = $storeId;
            }
        }

        $connection->beginTransaction();
        try {
            if (!empty($data)) {
                $connection->insertOnDuplicate($table, $data, ['label']);
            }

            if (!empty($deleteByStoreIds)) {
                $connection->delete($table, ['rule_id=?' => $ruleId, 'store_id IN (?)' => $deleteByStoreIds]);
            }
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
        $connection->commit();

        return $this;
    }

    /**
     * Get all existing rule labels
     *
     * @param int $ruleId
     * @return array
     * @since 2.0.0
     */
    public function getStoreLabels($ruleId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('salesrule_label'),
            ['store_id', 'label']
        )->where(
            'rule_id = :rule_id'
        );
        return $this->getConnection()->fetchPairs($select, [':rule_id' => $ruleId]);
    }

    /**
     * Get rule label by specific store id
     *
     * @param int $ruleId
     * @param int $storeId
     * @return string
     * @since 2.0.0
     */
    public function getStoreLabel($ruleId, $storeId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('salesrule_label'),
            'label'
        )->where(
            'rule_id = :rule_id'
        )->where(
            'store_id IN(0, :store_id)'
        )->order(
            'store_id DESC'
        );
        return $this->getConnection()->fetchOne($select, [':rule_id' => $ruleId, ':store_id' => $storeId]);
    }

    /**
     * Return codes of all product attributes currently used in promo rules
     *
     * @return array
     * @since 2.0.0
     */
    public function getActiveAttributes()
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['a' => $this->getTable('salesrule_product_attribute')],
            new \Zend_Db_Expr('DISTINCT ea.attribute_code')
        )->joinInner(
            ['ea' => $this->getTable('eav_attribute')],
            'ea.attribute_id = a.attribute_id',
            []
        );
        return $connection->fetchAll($select);
    }

    /**
     * Save product attributes currently used in conditions and actions of rule
     *
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param mixed $attributes
     * @return $this
     * @since 2.0.0
     */
    public function setActualProductAttributes($rule, $attributes)
    {
        $connection = $this->getConnection();
        $metadata = $this->metadataPool->getMetadata(RuleInterface::class);
        $connection->delete(
            $this->getTable('salesrule_product_attribute'),
            [$metadata->getLinkField() . '=?' => $rule->getData($metadata->getLinkField())]
        );

        //Getting attribute IDs for attribute codes
        $attributeIds = [];
        $select = $this->getConnection()->select()->from(
            ['a' => $this->getTable('eav_attribute')],
            ['a.attribute_id']
        )->where(
            'a.attribute_code IN (?)',
            [$attributes]
        );
        $attributesFound = $this->getConnection()->fetchAll($select);
        if ($attributesFound) {
            foreach ($attributesFound as $attribute) {
                $attributeIds[] = $attribute['attribute_id'];
            }

            $data = [];
            foreach ($rule->getCustomerGroupIds() as $customerGroupId) {
                foreach ($rule->getWebsiteIds() as $websiteId) {
                    foreach ($attributeIds as $attribute) {
                        $data[] = [
                            $metadata->getLinkField() => $rule->getData($metadata->getLinkField()),
                            'website_id' => $websiteId,
                            'customer_group_id' => $customerGroupId,
                            'attribute_id' => $attribute,
                        ];
                    }
                }
            }
            $connection->insertMultiple($this->getTable('salesrule_product_attribute'), $data);
        }

        return $this;
    }

    /**
     * Collect all product attributes used in serialized rule's action or condition
     *
     * @param string $serializedString
     * @return array
     * @since 2.0.0
     */
    public function getProductAttributes($serializedString)
    {
        // we need 4 backslashes to match 1 in regexp, see http://www.php.net/manual/en/regexp.reference.escape.php
        preg_match_all(
            '~"Magento\\\\\\\\SalesRule\\\\\\\\Model\\\\\\\\Rule\\\\\\\\Condition\\\\\\\\Product","attribute":"(.*?)"~',
            $serializedString,
            $matches
        );
        // we always have $matches like [[],[]]
        return array_values($matches[1]);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @since 2.1.0
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->getEntityManager()->save($object);
        return $this;
    }

    /**
     * Delete the object
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @since 2.1.0
     */
    public function delete(AbstractModel $object)
    {
        $this->getEntityManager()->delete($object);
        return $this;
    }

    /**
     * @return \Magento\Framework\EntityManager\EntityManager
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    private function getEntityManager()
    {
        if (null === $this->entityManager) {
            $this->entityManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\EntityManager::class);
        }
        return $this->entityManager;
    }
}
