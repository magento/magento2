<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Abstract Tax Class
 */
namespace Magento\Tax\Model\TaxClass;

abstract class AbstractType extends \Magento\Framework\DataObject implements Type\TypeInterface
{
    /**
     * @var \Magento\Tax\Model\Calculation\Rule
     */
    protected $_calculationRule;

    /**
     * Class Type
     *
     * @var string
     */
    protected $_classType;

    /**
     * @param \Magento\Tax\Model\Calculation\Rule $calculationRule
     * @param array $data
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
     */
    public function getAssignedToRules()
    {
        return $this->_calculationRule->getCollection()->setClassTypeFilter($this->_classType, $this->getId());
    }
}
