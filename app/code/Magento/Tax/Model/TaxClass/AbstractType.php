<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Abstract Tax Class
 */
namespace Magento\Tax\Model\TaxClass;

/**
 * Class \Magento\Tax\Model\TaxClass\AbstractType
 *
 * @since 2.0.0
 */
abstract class AbstractType extends \Magento\Framework\DataObject implements Type\TypeInterface
{
    /**
     * @var \Magento\Tax\Model\Calculation\Rule
     * @since 2.0.0
     */
    protected $_calculationRule;

    /**
     * Class Type
     *
     * @var string
     * @since 2.0.0
     */
    protected $_classType;

    /**
     * @param \Magento\Tax\Model\Calculation\Rule $calculationRule
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(\Magento\Tax\Model\Calculation\Rule $calculationRule, array $data = [])
    {
        parent::__construct($data);
        $this->_calculationRule = $calculationRule;
    }

    /**
     * Get Collection of Tax Rules that are assigned to this tax class
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @since 2.0.0
     */
    public function getAssignedToRules()
    {
        return $this->_calculationRule->getCollection()->setClassTypeFilter($this->_classType, $this->getId());
    }
}
