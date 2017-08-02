<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rule\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;

/**
 * Abstract Rule entity data model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 2.0.0
 */
abstract class AbstractModel extends \Magento\Framework\Model\AbstractExtensibleModel
{
    /**
     * Store rule combine conditions model
     *
     * @var \Magento\Rule\Model\Condition\Combine
     * @since 2.0.0
     */
    protected $_conditions;

    /**
     * Store rule actions model
     *
     * @var \Magento\Rule\Model\Action\Collection
     * @since 2.0.0
     */
    protected $_actions;

    /**
     * Store rule form instance
     *
     * @var \Magento\Framework\Data\Form
     * @since 2.0.0
     */
    protected $_form;

    /**
     * Is model can be deleted flag
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_isDeleteable = true;

    /**
     * Is model readonly
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_isReadonly = false;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     * @since 2.2.0
     */
    protected $serializer;

    /**
     * Getter for rule combine conditions instance
     *
     * @return \Magento\Rule\Model\Condition\Combine
     * @since 2.0.0
     */
    abstract public function getConditionsInstance();

    /**
     * Getter for rule actions collection instance
     *
     * @return \Magento\Rule\Model\Action\Collection
     * @since 2.0.0
     */
    abstract public function getActionsInstance();

    /**
     * Form factory
     *
     * @var \Magento\Framework\Data\FormFactory
     * @since 2.0.0
     */
    protected $_formFactory;

    /**
     * Timezone instance
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     * @since 2.0.0
     */
    protected $_localeDate;

    /**
     * AbstractModel constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param ExtensionAttributesFactory|null $extensionFactory
     * @param AttributeValueFactory|null $customAttributeFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        ExtensionAttributesFactory $extensionFactory = null,
        AttributeValueFactory $customAttributeFactory = null,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->_formFactory = $formFactory;
        $this->_localeDate = $localeDate;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\Serialize\Serializer\Json::class
        );
        parent::__construct(
            $context,
            $registry,
            $extensionFactory ?: $this->getExtensionFactory(),
            $customAttributeFactory ?: $this->getCustomAttributeFactory(),
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Prepare data before saving
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    public function beforeSave()
    {
        // Check if discount amount not negative
        if ($this->hasDiscountAmount()) {
            if ((int)$this->getDiscountAmount() < 0) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Please choose a valid discount amount.'));
            }
        }

        // Serialize conditions
        if ($this->getConditions()) {
            $this->setConditionsSerialized($this->serializer->serialize($this->getConditions()->asArray()));
            $this->_conditions = null;
        }

        // Serialize actions
        if ($this->getActions()) {
            $this->setActionsSerialized($this->serializer->serialize($this->getActions()->asArray()));
            $this->_actions = null;
        }

        /**
         * Prepare website Ids if applicable and if they were set as string in comma separated format.
         * Backwards compatibility.
         */
        if ($this->hasWebsiteIds()) {
            $websiteIds = $this->getWebsiteIds();
            if (is_string($websiteIds) && !empty($websiteIds)) {
                $this->setWebsiteIds(explode(',', $websiteIds));
            }
        }

        /**
         * Prepare customer group Ids if applicable and if they were set as string in comma separated format.
         * Backwards compatibility.
         */
        if ($this->hasCustomerGroupIds()) {
            $groupIds = $this->getCustomerGroupIds();
            if (is_string($groupIds) && !empty($groupIds)) {
                $this->setCustomerGroupIds(explode(',', $groupIds));
            }
        }

        parent::beforeSave();
        return $this;
    }

    /**
     * Set rule combine conditions model
     *
     * @param \Magento\Rule\Model\Condition\Combine $conditions
     * @return $this
     * @since 2.0.0
     */
    public function setConditions($conditions)
    {
        $this->_conditions = $conditions;
        return $this;
    }

    /**
     * Retrieve rule combine conditions model
     *
     * @return \Magento\Rule\Model\Condition\Combine
     * @since 2.0.0
     */
    public function getConditions()
    {
        if (empty($this->_conditions)) {
            $this->_resetConditions();
        }

        // Load rule conditions if it is applicable
        if ($this->hasConditionsSerialized()) {
            $conditions = $this->getConditionsSerialized();
            if (!empty($conditions)) {
                $conditions = $this->serializer->unserialize($conditions);
                if (is_array($conditions) && !empty($conditions)) {
                    $this->_conditions->loadArray($conditions);
                }
            }
            $this->unsConditionsSerialized();
        }

        return $this->_conditions;
    }

    /**
     * Set rule actions model
     *
     * @param \Magento\Rule\Model\Action\Collection $actions
     * @return $this
     * @since 2.0.0
     */
    public function setActions($actions)
    {
        $this->_actions = $actions;
        return $this;
    }

    /**
     * Retrieve rule actions model
     *
     * @return \Magento\Rule\Model\Action\Collection
     * @since 2.0.0
     */
    public function getActions()
    {
        if (!$this->_actions) {
            $this->_resetActions();
        }

        // Load rule actions if it is applicable
        if ($this->hasActionsSerialized()) {
            $actions = $this->getActionsSerialized();
            if (!empty($actions)) {
                $actions = $this->serializer->unserialize($actions);
                if (is_array($actions) && !empty($actions)) {
                    $this->_actions->loadArray($actions);
                }
            }
            $this->unsActionsSerialized();
        }

        return $this->_actions;
    }

    /**
     * Reset rule combine conditions
     *
     * @param null|\Magento\Rule\Model\Condition\Combine $conditions
     * @return $this
     * @since 2.0.0
     */
    protected function _resetConditions($conditions = null)
    {
        if (null === $conditions) {
            $conditions = $this->getConditionsInstance();
        }
        $conditions->setRule($this)->setId('1')->setPrefix('conditions');
        $this->setConditions($conditions);

        return $this;
    }

    /**
     * Reset rule actions
     *
     * @param null|\Magento\Rule\Model\Action\Collection $actions
     * @return $this
     * @since 2.0.0
     */
    protected function _resetActions($actions = null)
    {
        if (null === $actions) {
            $actions = $this->getActionsInstance();
        }
        $actions->setRule($this)->setId('1')->setPrefix('actions');
        $this->setActions($actions);

        return $this;
    }

    /**
     * Rule form getter
     *
     * @return \Magento\Framework\Data\Form
     * @since 2.0.0
     */
    public function getForm()
    {
        if (!$this->_form) {
            $this->_form = $this->_formFactory->create();
        }
        return $this->_form;
    }

    /**
     * Initialize rule model data from array
     *
     * @param array $data
     * @return $this
     * @since 2.0.0
     */
    public function loadPost(array $data)
    {
        $arr = $this->_convertFlatToRecursive($data);
        if (isset($arr['conditions'])) {
            $this->getConditions()->setConditions([])->loadArray($arr['conditions'][1]);
        }
        if (isset($arr['actions'])) {
            $this->getActions()->setActions([])->loadArray($arr['actions'][1], 'actions');
        }

        return $this;
    }

    /**
     * Set specified data to current rule.
     * Set conditions and actions recursively.
     * Convert dates into \DateTime.
     *
     * @param array $data
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    protected function _convertFlatToRecursive(array $data)
    {
        $arr = [];
        foreach ($data as $key => $value) {
            if (($key === 'conditions' || $key === 'actions') && is_array($value)) {
                foreach ($value as $id => $data) {
                    $path = explode('--', $id);
                    $node = & $arr;
                    for ($i = 0, $l = sizeof($path); $i < $l; $i++) {
                        if (!isset($node[$key][$path[$i]])) {
                            $node[$key][$path[$i]] = [];
                        }
                        $node = & $node[$key][$path[$i]];
                    }
                    foreach ($data as $k => $v) {
                        $node[$k] = $v;
                    }
                }
            } else {
                /**
                 * Convert dates into \DateTime
                 */
                if (in_array($key, ['from_date', 'to_date'], true) && $value) {
                    $value = new \DateTime($value);
                }
                $this->setData($key, $value);
            }
        }

        return $arr;
    }

    /**
     * Validate rule conditions to determine if rule can run
     *
     * @param \Magento\Framework\DataObject $object
     * @return bool
     * @since 2.0.0
     */
    public function validate(\Magento\Framework\DataObject $object)
    {
        return $this->getConditions()->validate($object);
    }

    /**
     * Validate rule data
     *
     * @param \Magento\Framework\DataObject $dataObject
     * @return bool|string[] - return true if validation passed successfully. Array with errors description otherwise
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function validateData(\Magento\Framework\DataObject $dataObject)
    {
        $result = [];
        $fromDate = $toDate = null;

        if ($dataObject->hasFromDate() && $dataObject->hasToDate()) {
            $fromDate = $dataObject->getFromDate();
            $toDate = $dataObject->getToDate();
        }

        if ($fromDate && $toDate) {
            $fromDate = new \DateTime($fromDate);
            $toDate = new \DateTime($toDate);

            if ($fromDate > $toDate) {
                $result[] = __('End Date must follow Start Date.');
            }
        }

        if ($dataObject->hasWebsiteIds()) {
            $websiteIds = $dataObject->getWebsiteIds();
            if (empty($websiteIds)) {
                $result[] = __('Please specify a website.');
            }
        }
        if ($dataObject->hasCustomerGroupIds()) {
            $customerGroupIds = $dataObject->getCustomerGroupIds();
            if (empty($customerGroupIds)) {
                $result[] = __('Please specify Customer Groups.');
            }
        }

        return !empty($result) ? $result : true;
    }

    /**
     * Check availability to delete rule
     *
     * @return bool
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function isDeleteable()
    {
        return $this->_isDeleteable;
    }

    /**
     * Set is rule can be deleted flag
     *
     * @param bool $value
     * @return $this
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function setIsDeleteable($value)
    {
        $this->_isDeleteable = (bool)$value;
        return $this;
    }

    /**
     * Check if rule is readonly
     *
     * @return bool
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function isReadonly()
    {
        return $this->_isReadonly;
    }

    /**
     * Set is readonly flag to rule
     *
     * @param bool $value
     * @return $this
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function setIsReadonly($value)
    {
        $this->_isReadonly = (bool)$value;
        return $this;
    }

    /**
     * Get rule associated website Ids
     *
     * @return array
     * @since 2.0.0
     */
    public function getWebsiteIds()
    {
        if (!$this->hasWebsiteIds()) {
            $websiteIds = $this->_getResource()->getWebsiteIds($this->getId());
            $this->setData('website_ids', (array)$websiteIds);
        }
        return $this->_getData('website_ids');
    }

    /**
     * @return \Magento\Framework\Api\ExtensionAttributesFactory
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    private function getExtensionFactory()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Api\ExtensionAttributesFactory::class);
    }

    /**
     * @return \Magento\Framework\Api\AttributeValueFactory
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    private function getCustomAttributeFactory()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Api\AttributeValueFactory::class);
    }
}
