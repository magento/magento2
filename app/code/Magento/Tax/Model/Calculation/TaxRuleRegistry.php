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

use Magento\Tax\Model\Calculation\RuleFactory as TaxRuleModelFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Model\Calculation\Rule as TaxRuleModel;

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
