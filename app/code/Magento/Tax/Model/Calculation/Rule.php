<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Calculation;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Tax\Api\Data\TaxRuleInterface;

/**
 * Tax Rule Model
 *
 * @method \Magento\Tax\Model\ResourceModel\Calculation\Rule _getResource()
 * @method \Magento\Tax\Model\ResourceModel\Calculation\Rule getResource()
 * @since 2.0.0
 */
class Rule extends \Magento\Framework\Model\AbstractExtensibleModel implements TaxRuleInterface
{
    /**#@+
     *
     * Tax rule field key.
     */
    const KEY_ID       = 'id';
    const KEY_CODE     = 'code';
    const KEY_PRIORITY = 'priority';
    const KEY_POSITION = 'position';
    const KEY_CUSTOMER_TAX_CLASS_IDS = 'customer_tax_class_ids';
    const KEY_PRODUCT_TAX_CLASS_IDS  = 'product_tax_class_ids';
    const KEY_TAX_RATE_IDS           = 'tax_rate_ids';
    const KEY_CALCULATE_SUBTOTAL     = 'calculate_subtotal';
    /**#@-*/

    /**
     * Prefix of model events names
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'tax_rule';

    /**
     * Tax Model Class
     *
     * @var \Magento\Tax\Model\ClassModel
     * @since 2.0.0
     */
    protected $_taxClass;

    /**
     * @var \Magento\Tax\Model\Calculation
     * @since 2.0.0
     */
    protected $_calculation;

    /**
     * @var \Magento\Tax\Model\Calculation\Rule\Validator
     * @since 2.0.0
     */
    protected $validator;

    /**
     * Name of object id field
     *
     * @var string
     * @since 2.0.0
     */
    protected $_idFieldName = 'tax_calculation_rule_id';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Tax\Model\ClassModel $taxClass
     * @param \Magento\Tax\Model\Calculation $calculation
     * @param Rule\Validator $validator
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Tax\Model\ClassModel $taxClass,
        \Magento\Tax\Model\Calculation $calculation,
        \Magento\Tax\Model\Calculation\Rule\Validator $validator,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_calculation = $calculation;
        $this->validator = $validator;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_init(\Magento\Tax\Model\ResourceModel\Calculation\Rule::class);
        $this->_taxClass = $taxClass;
    }

    /**
     * After save rule
     * Re-declared for populate rate calculations
     *
     * @return $this
     * @since 2.0.0
     */
    public function afterSave()
    {
        parent::afterSave();
        $this->saveCalculationData();
        $this->_eventManager->dispatch('tax_settings_change_after');
        return $this;
    }

    /**
     * After rule delete
     * Re-declared for dispatch tax_settings_change_after event
     *
     * @return $this
     * @since 2.0.0
     */
    public function afterDelete()
    {
        $this->_eventManager->dispatch('tax_settings_change_after');
        return parent::afterDelete();
    }

    /**
     * @return void
     * @since 2.0.0
     */
    public function saveCalculationData()
    {
        $ctc = $this->getData('customer_tax_class_ids');
        $ptc = $this->getData('product_tax_class_ids');
        $rates = $this->getData('tax_rate_ids');

        $this->_calculation->deleteByRuleId($this->getId());
        foreach ($ctc as $c) {
            foreach ($ptc as $p) {
                foreach ($rates as $r) {
                    $dataArray = [
                        'tax_calculation_rule_id' => $this->getId(),
                        'tax_calculation_rate_id' => $r,
                        'customer_tax_class_id' => $c,
                        'product_tax_class_id' => $p,
                    ];
                    $this->_calculation->setData($dataArray)->save();
                }
            }
        }
    }

    /**
     * @return \Magento\Tax\Model\Calculation
     * @since 2.0.0
     */
    public function getCalculationModel()
    {
        return $this->_calculation;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getRates()
    {
        return $this->getCalculationModel()->getRates($this->getId());
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getCustomerTaxClasses()
    {
        return $this->getCalculationModel()->getCustomerTaxClasses($this->getId());
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getProductTaxClasses()
    {
        return $this->getCalculationModel()->getProductTaxClasses($this->getId());
    }

    /**
     * Fetches rules by rate, customer tax class and product tax class
     * and product tax class combination
     *
     * @param array $rateId
     * @param array $customerTaxClassIds
     * @param array $productTaxClassIds
     * @return array
     * @since 2.0.0
     */
    public function fetchRuleCodes($rateId, $customerTaxClassIds, $productTaxClassIds)
    {
        return $this->getResource()->fetchRuleCodes($rateId, $customerTaxClassIds, $productTaxClassIds);
    }

    /**
     * @codeCoverageIgnoreStart
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCode()
    {
        return $this->getData(self::KEY_CODE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getPosition()
    {
        return (int) $this->getData(self::KEY_POSITION);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCalculateSubtotal()
    {
        return (bool) $this->getData(self::KEY_CALCULATE_SUBTOTAL);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getPriority()
    {
        return $this->getData(self::KEY_PRIORITY);
    }

    //@codeCoverageIgnoreEnd

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCustomerTaxClassIds()
    {
        $ids = $this->getData(self::KEY_CUSTOMER_TAX_CLASS_IDS);
        if (null === $ids) {
            $ids = $this->_getUniqueValues($this->getCustomerTaxClasses());
            $this->setData(self::KEY_CUSTOMER_TAX_CLASS_IDS, $ids);
        }
        return $ids;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getProductTaxClassIds()
    {
        $ids = $this->getData(self::KEY_PRODUCT_TAX_CLASS_IDS);
        if (null === $ids) {
            $ids = $this->_getUniqueValues($this->getProductTaxClasses());
            $this->setData(self::KEY_PRODUCT_TAX_CLASS_IDS, $ids);
        }
        return $ids;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getTaxRateIds()
    {
        $ids = $this->getData(self::KEY_TAX_RATE_IDS);
        if (null === $ids) {
            $ids = $this->_getUniqueValues($this->getRates());
            $this->setData(self::KEY_TAX_RATE_IDS, $ids);
        }
        return $ids;
    }

    /**
     * Get unique values of indexed array.
     *
     * @param array|null $values
     * @return array|null
     * @since 2.0.0
     */
    protected function _getUniqueValues($values)
    {
        if (!$values) {
            return null;
        }
        return array_values(array_unique($values));
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function _getValidationRulesBeforeSave()
    {
        return $this->validator;
    }

    /**
     * Set tax rule code
     *
     * @param string $code
     * @return $this
     * @since 2.0.0
     */
    public function setCode($code)
    {
        return $this->setData(self::KEY_CODE, $code);
    }

    /**
     * Set priority
     *
     * @param int $priority
     * @return $this
     * @since 2.0.0
     */
    public function setPriority($priority)
    {
        return $this->setData(self::KEY_PRIORITY, $priority);
    }

    /**
     * Set sort order.
     *
     * @param int $position
     * @return $this
     * @since 2.0.0
     */
    public function setPosition($position)
    {
        return $this->setData(self::KEY_POSITION, $position);
    }

    /**
     * Set customer tax class id
     *
     * @param int[] $customerTaxClassIds
     * @return $this
     * @since 2.0.0
     */
    public function setCustomerTaxClassIds(array $customerTaxClassIds = null)
    {
        return $this->setData(self::KEY_CUSTOMER_TAX_CLASS_IDS, $customerTaxClassIds);
    }

    /**
     * Set product tax class id
     *
     * @param int[] $productTaxClassIds
     * @return $this
     * @since 2.0.0
     */
    public function setProductTaxClassIds(array $productTaxClassIds = null)
    {
        return $this->setData(self::KEY_PRODUCT_TAX_CLASS_IDS, $productTaxClassIds);
    }

    /**
     * Set tax rate ids
     *
     * @param int[] $taxRateIds
     * @return $this
     * @since 2.0.0
     */
    public function setTaxRateIds(array $taxRateIds = null)
    {
        return $this->setData(self::KEY_TAX_RATE_IDS, $taxRateIds);
    }

    /**
     * Set calculate subtotal.
     *
     * @param bool $calculateSubtotal
     * @return $this
     * @since 2.0.0
     */
    public function setCalculateSubtotal($calculateSubtotal)
    {
        return $this->setData(self::KEY_CALCULATE_SUBTOTAL, $calculateSubtotal);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Tax\Api\Data\TaxRuleExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Tax\Api\Data\TaxRuleExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxRuleExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
