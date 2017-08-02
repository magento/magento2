<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Model\ClassModel as TaxClassModel;
use Magento\Tax\Model\ClassModelFactory as TaxClassModelFactory;

/**
 * Registry for the tax class models
 * @since 2.0.0
 */
class ClassModelRegistry
{
    /**
     * Tax class model factory
     *
     * @var TaxClassModelFactory
     * @since 2.0.0
     */
    private $taxClassModelFactory;

    /**
     * Tax class models
     *
     * @var TaxClassModel[]
     * @since 2.0.0
     */
    private $taxClassRegistryById = [];

    /**
     * Initialize dependencies
     *
     * @param TaxClassModelFactory $taxClassModelFactory
     * @since 2.0.0
     */
    public function __construct(TaxClassModelFactory $taxClassModelFactory)
    {
        $this->taxClassModelFactory = $taxClassModelFactory;
    }

    /**
     * Add tax class model to the registry
     *
     * @param TaxClassModel $taxClassModel
     * @return void
     * @since 2.0.0
     */
    public function registerTaxClass(TaxClassModel $taxClassModel)
    {
        $this->taxClassRegistryById[$taxClassModel->getId()] = $taxClassModel;
    }

    /**
     * Retrieve tax class model from the registry
     *
     * @param int $taxClassId
     * @return TaxClassModel
     * @throws NoSuchEntityException
     * @since 2.0.0
     */
    public function retrieve($taxClassId)
    {
        if (isset($this->taxClassRegistryById[$taxClassId])) {
            return $this->taxClassRegistryById[$taxClassId];
        }
        /** @var TaxClassModel $taxClassModel */
        $taxClassModel = $this->taxClassModelFactory->create()->load($taxClassId);
        if (!$taxClassModel->getId()) {
            // tax class does not exist
            throw NoSuchEntityException::singleField(TaxClassModel::KEY_ID, $taxClassId);
        }
        $this->taxClassRegistryById[$taxClassModel->getId()] = $taxClassModel;
        return $taxClassModel;
    }

    /**
     * Remove an instance of the tax class model from the registry
     *
     * @param int $taxClassId
     * @return void
     * @since 2.0.0
     */
    public function remove($taxClassId)
    {
        unset($this->taxClassRegistryById[$taxClassId]);
    }
}
