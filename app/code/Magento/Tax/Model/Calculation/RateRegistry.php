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

use Magento\Tax\Model\Calculation\RateFactory as TaxRateModelFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Model\Calculation\Rate as TaxRateModel;

class RateRegistry
{
    /**
     * Tax rate model factory
     *
     * @var  TaxRateModelFactory
     */
    private $taxRateModelFactory;

    /**
     * Tax rate models
     *
     * @var TaxRateModel[]
     */
    private $taxRateRegistryById = [];

    /**
     * Constructor
     *
     * @param TaxRateModelFactory $taxModelRateFactory
     */
    public function __construct(
        TaxRateModelFactory $taxModelRateFactory
    ) {
        $this->taxRateModelFactory = $taxModelRateFactory;
    }

    /**
     * Register TaxRate Model to registry
     *
     * @param TaxRateModel $taxRateModel
     * @return void
     */
    public function registerTaxRate(TaxRateModel $taxRateModel)
    {
        $this->taxRateRegistryById[$taxRateModel->getId()] = $taxRateModel;
    }

    /**
     * Retrieve TaxRate Model from registry given an id
     *
     * @param int $taxRateId
     * @return TaxRateModel
     * @throws NoSuchEntityException
     */
    public function retrieveTaxRate($taxRateId)
    {
        if (isset($this->taxRateRegistryById[$taxRateId])) {
            return $this->taxRateRegistryById[$taxRateId];
        }
        /** @var TaxRateModel $taxRateModel */
        $taxRateModel = $this->taxRateModelFactory->create()->load($taxRateId);
        if (!$taxRateModel->getId()) {
            // tax rate does not exist
            throw NoSuchEntityException::singleField('taxRateId', $taxRateId);
        }
        $this->taxRateRegistryById[$taxRateModel->getId()] = $taxRateModel;
        return $taxRateModel;
    }

    /**
     * Remove an instance of the TaxRate Model from the registry
     *
     * @param int $taxRateId
     * @return void
     */
    public function removeTaxRate($taxRateId)
    {
        unset($this->taxRateRegistryById[$taxRateId]);
    }
}
