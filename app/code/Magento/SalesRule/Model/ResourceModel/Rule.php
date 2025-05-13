<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
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
 */
class Rule extends AbstractResource
{
    /**
     * Store associated with rule entities information map
     *
     * @var array
     */
    protected $_associatedEntitiesMap = [];

    /**
     * @var array
     */
    protected $customerGroupIds = [];

    /**
     * @var array
     */
    protected $websiteIds = [];

    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon
     */
    protected $_resourceCoupon;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var string
     */
    private $linkedField;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\SalesRule\Model\ResourceModel\Coupon $resourceCoupon
     * @param string $connectionName
     * @param \Magento\Framework\DataObject|null $associatedEntityMapInstance
     * @param Json $serializer Optional parameter for backward compatibility
     * @param MetadataPool $metadataPool Optional parameter for backward compatibility
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\SalesRule\Model\ResourceModel\Coupon $resourceCoupon,
        $connectionName = null,
        ?\Magento\Framework\DataObject $associatedEntityMapInstance = null,
        ?Json $serializer = null,
        ?MetadataPool $metadataPool = null
    ) {
        $this->string = $string;
        $this->_resourceCoupon = $resourceCoupon;
        $associatedEntitiesMapInstance = $associatedEntityMapInstance ?: ObjectManager::getInstance()->get(
            // @phpstan-ignore-next-line - this is a virtual type defined in di.xml
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
     */
    protected function _construct()
    {
        $this->_init('salesrule', 'rule_id');
    }

    /**
     * Load customer group IDs for a rule
     *
     * @param AbstractModel $object
     * @return void
     */
    public function loadCustomerGroupIds(AbstractModel $object)
    {
        if (!$this->customerGroupIds) {
            $this->customerGroupIds = (array)$this->getCustomerGroupIds($object->getId());
        }
        $object->setData('customer_group_ids', $this->customerGroupIds);
    }

    /**
     * Load website IDs for a rule
     *
     * @param AbstractModel $object
     * @return void
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
     */
    protected function _afterSave(AbstractModel $object)
    {
        if ($object->hasStoreLabels()) {
            $this->saveStoreLabels($object->getData($this->getLinkField()), $object->getStoreLabels());
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
        if (($object->getUseAutoGeneration()
            || ((int) $object->getCouponType()) === \Magento\SalesRule\Model\Rule::COUPON_TYPE_AUTO
            ) && $object->hasDataChanges()
        ) {
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
     */
    public function saveStoreLabels($ruleId, $labels)
    {
        $deleteByStoreIds = [];
        $table = $this->getTable('salesrule_label');
        $connection = $this->getConnection();

        $data = [];
        foreach ($labels as $storeId => $label) {
            if ($this->string->strlen($label)) {
                $data[] = [$this->getLinkField() => $ruleId, 'store_id' => $storeId, 'label' => $label];
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
                $connection->delete(
                    $table,
                    [$this->getLinkField() . '=?' => $ruleId, 'store_id IN (?)' => $deleteByStoreIds]
                );
            }
        } catch (\Exception $e) {
            $connection->rollBack();
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
     */
    public function getStoreLabels($ruleId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('salesrule_label'),
            ['store_id', 'label']
        )->where(
            $this->getLinkField() . ' = :rule_id'
        );
        return $this->getConnection()->fetchPairs($select, [':rule_id' => $ruleId]);
    }

    /**
     * Get rule label by specific store id
     *
     * @param int $ruleId
     * @param int $storeId
     * @return string
     */
    public function getStoreLabel($ruleId, $storeId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('salesrule_label'),
            'label'
        )->where(
            $this->getLinkField() . ' = :rule_id'
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
     */
    public function getActiveAttributes()
    {
        $connection = $this->getConnection();
        $subSelect = $connection->select();
        $subSelect->reset();
        $subSelect->from($this->getTable('salesrule_product_attribute'))
            ->group('attribute_id');
        $select = $connection->select()->from(
            ['a' => $subSelect],
            new \Zend_Db_Expr('ea.attribute_code')
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
     */
    public function setActualProductAttributes($rule, $attributes)
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getTable('salesrule_product_attribute'),
            [$this->getLinkField() . '=?' => $rule->getData($this->getLinkField())]
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
                            $this->getLinkField() => $rule->getData($this->getLinkField()),
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
     * Save cart rule
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
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
     */
    public function delete(AbstractModel $object)
    {
        $this->getEntityManager()->delete($object);
        return $this;
    }

    /**
     * Init EntityManager
     *
     * @return \Magento\Framework\EntityManager\EntityManager
     */
    private function getEntityManager()
    {
        if (null === $this->entityManager) {
            $this->entityManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\EntityManager::class);
        }
        return $this->entityManager;
    }

    /**
     * Get lined field for Rule entity
     *
     * @return string
     */
    public function getLinkField() :string
    {
        if ($this->linkedField === null) {
            $metadata = $this->metadataPool->getMetadata(RuleInterface::class);
            $this->linkedField = $metadata->getLinkField();
        }

        return $this->linkedField;
    }
}
