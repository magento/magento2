<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tax Class factory
 */
namespace Magento\Tax\Model\TaxClass;

/**
 * Class \Magento\Tax\Model\TaxClass\Factory
 *
 * @since 2.0.0
 */
class Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * Type to class map
     *
     * @var array
     * @since 2.0.0
     */
    protected $_types = [
        \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER => \Magento\Tax\Model\TaxClass\Type\Customer::class,
        \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT => \Magento\Tax\Model\TaxClass\Type\Product::class,
    ];

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new config object
     *
     * @param \Magento\Tax\Model\ClassModel $taxClass
     * @return \Magento\Tax\Model\TaxClass\Type\TypeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function create(\Magento\Tax\Model\ClassModel $taxClass)
    {
        $taxClassType = $taxClass->getClassType();
        if (!array_key_exists($taxClassType, $this->_types)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Invalid type of tax class "%1"', $taxClassType)
            );
        }
        return $this->_objectManager->create(
            $this->_types[$taxClassType],
            ['data' => ['id' => $taxClass->getId()]]
        );
    }
}
