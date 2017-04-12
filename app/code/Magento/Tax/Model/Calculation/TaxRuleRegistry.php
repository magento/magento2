<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Calculation;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Model\Calculation\Rule as TaxRuleModel;
use Magento\Tax\Model\Calculation\RuleFactory as TaxRuleModelFactory;

class TaxRuleRegistry
{
    /** @var  TaxRuleModelFactory */
    private $taxRuleModelFactory;

    /**
     * @var array taxRuleId => TaxRuleModel
     */
    private $registry = [];

    /**
     * Constructor
     *
     * @param TaxRuleModelFactory $taxRuleModelFactory
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
     */
    public function removeTaxRule($taxRuleId)
    {
        unset($this->registry[$taxRuleId]);
    }
}
