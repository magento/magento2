<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Calculation;

use Magento\Framework\Api\AttributeDataBuilder;
use Magento\Framework\Api\MetadataServiceInterface;
use Magento\Tax\Api\Data\TaxRuleInterface;

/**
 * Tax Rule Model
 *
 * @method \Magento\Tax\Model\Resource\Calculation\Rule _getResource()
 * @method \Magento\Tax\Model\Resource\Calculation\Rule getResource()
 */
class Rule extends \Magento\Framework\Model\AbstractExtensibleModel implements TaxRuleInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'tax_rule';

    /**
     * Tax Model Class
     *
     * @var \Magento\Tax\Model\ClassModel
     */
    protected $_taxClass;

    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $_calculation;

    /**
     * @var \Magento\Tax\Model\Calculation\Rule\Validator
     */
    protected $validator;

    /**
     * Name of object id field
     *
     * @var string
     */
    protected $_idFieldName = 'tax_calculation_rule_id';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param MetadataServiceInterface $metadataService
     * @param AttributeDataBuilder $customAttributeBuilder
     * @param \Magento\Tax\Model\ClassModel $taxClass
     * @param \Magento\Tax\Model\Calculation $calculation
     * @param Rule\Validator $validator
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        MetadataServiceInterface $metadataService,
        AttributeDataBuilder $customAttributeBuilder,
        \Magento\Tax\Model\ClassModel $taxClass,
        \Magento\Tax\Model\Calculation $calculation,
        \Magento\Tax\Model\Calculation\Rule\Validator $validator,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->_calculation = $calculation;
        $this->validator = $validator;
        parent::__construct(
            $context,
            $registry,
            $metadataService,
            $customAttributeBuilder,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_init('Magento\Tax\Model\Resource\Calculation\Rule');
        $this->_taxClass = $taxClass;
    }

    /**
     * After save rule
     * Re-declared for populate rate calculations
     *
     * @return $this
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
     */
    public function afterDelete()
    {
        $this->_eventManager->dispatch('tax_settings_change_after');
        return parent::afterDelete();
    }

    /**
     * @return void
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
     */
    public function getCalculationModel()
    {
        return $this->_calculation;
    }

    /**
     * @return array
     */
    public function getRates()
    {
        return $this->getCalculationModel()->getRates($this->getId());
    }

    /**
     * @return array
     */
    public function getCustomerTaxClasses()
    {
        return $this->getCalculationModel()->getCustomerTaxClasses($this->getId());
    }

    /**
     * @return array
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
     */
    public function fetchRuleCodes($rateId, $customerTaxClassIds, $productTaxClassIds)
    {
        return $this->getResource()->fetchRuleCodes($rateId, $customerTaxClassIds, $productTaxClassIds);
    }

    /**
     * @codeCoverageIgnoreStart
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getData('code');
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return (int) $this->getData('position');
    }

    /**
     * {@inheritdoc}
     */
    public function getCalculateSubtotal()
    {
        return (bool) $this->getData('calculate_subtotal');
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return $this->getData('priority');
    }
    //@codeCoverageIgnoreEnd

    /**
     * {@inheritdoc}
     */
    public function getCustomerTaxClassIds()
    {
        $ids = $this->getData('customer_tax_class_ids');
        if (null === $ids) {
            $ids = $this->_getUniqueValues($this->getCustomerTaxClasses());
            $this->setData('customer_tax_class_ids', $ids);
        }
        return $ids;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductTaxClassIds()
    {
        $ids = $this->getData('product_tax_class_ids');
        if (null === $ids) {
            $ids = $this->_getUniqueValues($this->getProductTaxClasses());
            $this->setData('product_tax_class_ids', $ids);
        }
        return $ids;
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxRateIds()
    {
        $ids = $this->getData('tax_rate_ids');
        if (null === $ids) {
            $ids = $this->_getUniqueValues($this->getRates());
            $this->setData('tax_rate_ids', $ids);
        }
        return $ids;
    }

    /**
     * Get unique values of indexed array.
     *
     * @param array|null $values
     * @return array|null
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
     */
    protected function _getValidationRulesBeforeSave()
    {
        return $this->validator;
    }
}
