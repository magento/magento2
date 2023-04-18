<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tax Class factory
 */
namespace Magento\Tax\Model\TaxClass;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Tax\Model\ClassModel as TaxClassModel;
use Magento\Tax\Model\TaxClass\Type\Customer as TaxClassTypeCustomer;
use Magento\Tax\Model\TaxClass\Type\Product as TaxClassTypeProduct;
use Magento\Tax\Model\TaxClass\Type\TypeInterface as TaxClassTypeInterface;

class Factory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Type to class map
     *
     * @var array
     */
    protected $_types = [
        TaxClassModel::TAX_CLASS_TYPE_CUSTOMER => TaxClassTypeCustomer::class,
        TaxClassModel::TAX_CLASS_TYPE_PRODUCT => TaxClassTypeProduct::class,
    ];

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new config object
     *
     * @param TaxClassModel $taxClass
     * @return TaxClassTypeInterface
     * @throws LocalizedException
     */
    public function create(TaxClassModel $taxClass)
    {
        $taxClassType = $taxClass->getClassType();
        if (!array_key_exists($taxClassType, $this->_types)) {
            throw new LocalizedException(
                __('Invalid type of tax class "%1"', $taxClassType)
            );
        }
        return $this->_objectManager->create(
            $this->_types[$taxClassType],
            ['data' => ['id' => $taxClass->getId()]]
        );
    }
}
