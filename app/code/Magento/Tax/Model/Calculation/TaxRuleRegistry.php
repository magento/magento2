<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Calculation;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Model\Calculation\Rule as TaxRuleModel;
use Magento\Tax\Model\Calculation\RuleFactory as TaxRuleModelFactory;

/**
 * Class \Magento\Tax\Model\Calculation\TaxRuleRegistry
 *
 * @since 2.0.0
 */
class TaxRuleRegistry
{
    /**
     * @var \Magento\Tax\Model\Calculation\RuleFactory
     * @since 2.0.0
     */
    private $taxRuleModelFactory;

    /**
     * @var array taxRuleId => TaxRuleModel
     * @since 2.0.0
     */
    private $registry = [];

    /**
     * Constructor
     *
     * @param TaxRuleModelFactory $taxRuleModelFactory
     * @since 2.0.0
     */
    public function __construct(
        TaxRuleModelFactory $taxRuleModelFactory
    ) {
        $this->taxRuleModelFactory = $taxRuleModelFactory;
    }

    /**
     * Registers TaxRule Model to registry
     *
     * @param TaxRuleModel $taxRuleModel
     * @return void
     * @since 2.0.0
     */
    public function registerTaxRule(TaxRuleModel $taxRuleModel)
    {
        $this->registry[$taxRuleModel->getId()] = $taxRuleModel;
    }

    /**
     * Retrieve TaxRule Model from registry given an id
     *
     * @param int $taxRuleId
     * @return TaxRuleModel
     * @throws NoSuchEntityException
     * @since 2.0.0
     */
    public function retrieveTaxRule($taxRuleId)
    {
        if (isset($this->registry[$taxRuleId])) {
            return $this->registry[$taxRuleId];
        }
        $taxRuleModel = $this->taxRuleModelFactory->create()->load($taxRuleId);
        if (!$taxRuleModel->getId()) {
            // tax rule does not exist
            throw NoSuchEntityException::singleField('taxRuleId', $taxRuleId);
        }
        $this->registry[$taxRuleModel->getId()] = $taxRuleModel;
        return $taxRuleModel;
    }

    /**
     * Remove an instance of the TaxRule Model from the registry
     *
     * @param int $taxRuleId
     * @return void
     * @since 2.0.0
     */
    public function removeTaxRule($taxRuleId)
    {
        unset($this->registry[$taxRuleId]);
    }
}
