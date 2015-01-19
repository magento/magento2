<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model;

use Magento\Tax\Api\Data\TaxClassDataBuilder;
use Magento\Tax\Api\Data\TaxClassInterface as TaxClass;
use Magento\Tax\Model\ClassModel as TaxClassModel;
use Magento\Tax\Model\ClassModelFactory as TaxClassFactory;

/**
 * Tax class converter. Allows conversion between tax class model and tax class service data object.
 */
class Converter
{
    /**
     * @var TaxClassDataBuilder
     */
    protected $taxClassBuilder;

    /**
     * @var TaxClassFactory
     */
    protected $taxClassFactory;

    /**
     * Initialize dependencies.
     *
     * @param TaxClassDataBuilder $taxClassBuilder
     * @param TaxClassFactory $taxClassFactory
     */
    public function __construct(TaxClassDataBuilder $taxClassBuilder, TaxClassFactory $taxClassFactory)
    {
        $this->taxClassBuilder = $taxClassBuilder;
        $this->taxClassFactory = $taxClassFactory;
    }

    /**
     * Convert tax class model into tax class service data object.
     *
     * @param TaxClassModel $taxClassModel
     * @return TaxClass
     */
    public function createTaxClassData(TaxClassModel $taxClassModel)
    {
        $this->taxClassBuilder
            ->setClassId($taxClassModel->getId())
            ->setClassName($taxClassModel->getClassName())
            ->setClassType($taxClassModel->getClassType());
        return $this->taxClassBuilder->create();
    }

    /**
     * Convert tax class service data object into tax class model.
     *
     * @param TaxClass $taxClass
     * @return TaxClassModel
     */
    public function createTaxClassModel(TaxClass $taxClass)
    {
        /** @var TaxClassModel $taxClassModel */
        $taxClassModel = $this->taxClassFactory->create();
        $taxClassModel
            ->setId($taxClass->getClassId())
            ->setClassName($taxClass->getClassName())
            ->setClassType($taxClass->getClassType());
        return $taxClassModel;
    }
}
