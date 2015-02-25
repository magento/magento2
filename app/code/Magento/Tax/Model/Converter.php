<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model;

use Magento\Tax\Api\Data\TaxClassInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassInterface as TaxClass;
use Magento\Tax\Model\ClassModel as TaxClassModel;
use Magento\Tax\Model\ClassModelFactory as TaxClassFactory;

/**
 * Tax class converter. Allows conversion between tax class model and tax class service data object.
 */
class Converter
{
    /**
     * @var TaxClassInterfaceFactory
     */
    protected $taxClassDataObjectFactory;

    /**
     * @var TaxClassFactory
     */
    protected $taxClassFactory;

    /**
     * Initialize dependencies.
     *
     * @param TaxClassInterfaceFactory $taxClassDataObjectFactory
     * @param TaxClassFactory $taxClassFactory
     */
    public function __construct(TaxClassInterfaceFactory $taxClassDataObjectFactory, TaxClassFactory $taxClassFactory)
    {
        $this->taxClassDataObjectFactory = $taxClassDataObjectFactory;
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
        return $this->taxClassDataObjectFactory->create()
            ->setClassId($taxClassModel->getId())
            ->setClassName($taxClassModel->getClassName())
            ->setClassType($taxClassModel->getClassType());
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
