<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tax\Model\Calculation;

use Magento\Tax\Model\Calculation\Rule as TaxRuleModel;
use Magento\Tax\Model\Calculation\RuleFactory as TaxRuleModelFactory;
use Magento\Tax\Service\V1\Data\TaxRule as TaxRuleDataObject;
use Magento\Tax\Service\V1\Data\TaxRuleBuilder as TaxRuleDataObjectBuilder;

/**
 * Tax Rule Model converter.
 *
 * Converts a Tax Rule Model to a Data Object or vice versa.
 */
class TaxRuleConverter
{
    /**
     * @var TaxRuleDataObjectBuilder
     */
    protected $taxRuleDataObjectBuilder;

    /**
     * @var TaxRuleModelFactory
     */
    protected $taxRuleModelFactory;

    /**
     * @param TaxRuleDataObjectBuilder $taxRuleDataObjectBuilder
     * @param TaxRuleModelFactory $taxRuleModelFactory
     */
    public function __construct(
        TaxRuleDataObjectBuilder $taxRuleDataObjectBuilder,
        TaxRuleModelFactory $taxRuleModelFactory
    ) {
        $this->taxRuleDataObjectBuilder = $taxRuleDataObjectBuilder;
        $this->taxRuleModelFactory = $taxRuleModelFactory;
    }

    /**
     * Convert a rate model to a TaxRate data object
     *
     * @param TaxRuleModel $ruleModel
     * @return TaxRuleDataObject
     */
    public function createTaxRuleDataObjectFromModel(TaxRuleModel $ruleModel)
    {
        if (!is_null($ruleModel->getId())) {
            $this->taxRuleDataObjectBuilder->setId($ruleModel->getId());
        }
        if (!is_null($ruleModel->getCode())) {
            $this->taxRuleDataObjectBuilder->setCode($ruleModel->getCode());
        }
        if (!is_null($ruleModel->getCustomerTaxClasses())) {
            $this->taxRuleDataObjectBuilder->setCustomerTaxClassIds(
                $this->_getUniqueValues($ruleModel->getCustomerTaxClasses())
            );
        }
        if (!is_null($ruleModel->getProductTaxClasses())) {
            $this->taxRuleDataObjectBuilder->setProductTaxClassIds(
                $this->_getUniqueValues($ruleModel->getProductTaxClasses())
            );
        }
        if (!is_null($ruleModel->getRates())) {
            $this->taxRuleDataObjectBuilder->setTaxRateIds($this->_getUniqueValues($ruleModel->getRates()));
        }
        if (!is_null($ruleModel->getPriority())) {
            $this->taxRuleDataObjectBuilder->setPriority($ruleModel->getPriority());
        }
        if (!is_null($ruleModel->getPosition())) {
            $this->taxRuleDataObjectBuilder->setSortOrder($ruleModel->getPosition());
        }
        if (!is_null($ruleModel->getCalculateSubtotal())) {
            $this->taxRuleDataObjectBuilder->setCalculateSubtotal($ruleModel->getCalculateSubtotal());
        }
        return $this->taxRuleDataObjectBuilder->create();
    }

    /**
     * Convert a tax rule data object to tax rule model
     *
     * @param TaxRuleDataObject $taxRule
     * @return TaxRuleModel
     */
    public function createTaxRuleModel(TaxRuleDataObject $taxRuleDataObject)
    {
        $taxRuleModel = $this->taxRuleModelFactory->create();
        $ruleId = $taxRuleDataObject->getId();
        if ($ruleId) {
            $taxRuleModel->setId($ruleId);
        }
        $taxRuleModel->setTaxCustomerClass($taxRuleDataObject->getCustomerTaxClassIds());
        $taxRuleModel->setTaxProductClass($taxRuleDataObject->getProductTaxClassIds());
        $taxRuleModel->setTaxRate($taxRuleDataObject->getTaxRateIds());
        $taxRuleModel->setCode($taxRuleDataObject->getCode());
        $taxRuleModel->setPriority($taxRuleDataObject->getPriority());
        $taxRuleModel->setPosition($taxRuleDataObject->getSortOrder());
        $taxRuleModel->setCalculateSubtotal($taxRuleDataObject->getCalculateSubtotal());
        return $taxRuleModel;
    }

    /**
     * Get unique values of indexed array.
     *
     * @param array $values
     * @return array
     */
    protected function _getUniqueValues($values)
    {
        return array_values(array_unique($values));
    }
}
