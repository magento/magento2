<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Abstract Tax Class
 */
namespace Magento\Tax\Model\TaxClass;

use Magento\Framework\DataObject;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Tax\Model\Calculation\Rule as CalculationRule;

abstract class AbstractType extends DataObject implements Type\TypeInterface
{
    /**
     * @var CalculationRule
     */
    protected $_calculationRule;

    /**
     * Class Type
     *
     * @var string
     */
    protected $_classType;

    /**
     * @param CalculationRule $calculationRule
     * @param array $data
     */
    public function __construct(CalculationRule $calculationRule, array $data = [])
    {
        parent::__construct($data);
        $this->_calculationRule = $calculationRule;
    }

    /**
     * Get Collection of Tax Rules that are assigned to this tax class
     *
     * @return AbstractCollection
     */
    public function getAssignedToRules()
    {
        return $this->_calculationRule->getCollection()->setClassTypeFilter($this->_classType, $this->getId());
    }
}
