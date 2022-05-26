<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product;

use Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Model\ClassModelFactory;
use Magento\Tax\Model\ResourceModel\TaxClass\Collection;
use Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory;

/**
 * Imported products tax class processor
 */
class TaxClassProcessor
{
    /**
     * Empty tax class name
     */
    private const CLASS_NONE_NAME = 'none';

    /**
     * Empty tax class ID
     */
    private const CLASS_NONE_ID = 0;

    /**
     * Tax attribute code.
     */
    const ATRR_CODE = 'tax_class_id';

    /**
     * Tax classes.
     *
     * @var array
     */
    protected $taxClasses;

    /**
     * Instance of tax class collection factory.
     *
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Instance of tax model factory.
     *
     * @var ClassModelFactory
     */
    protected $classModelFactory;

    /**
     * @param CollectionFactory $collectionFactory
     * @param ClassModelFactory $classModelFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ClassModelFactory $classModelFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->classModelFactory = $classModelFactory;
        $this->initTaxClasses();
    }

    /**
     * Initiate tax classes.
     *
     * @return $this
     */
    protected function initTaxClasses()
    {
        if (empty($this->taxClasses)) {
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('class_type', ClassModel::TAX_CLASS_TYPE_PRODUCT);
            /* @var $collection Collection */
            foreach ($collection as $taxClass) {
                $this->taxClasses[mb_strtolower($taxClass->getClassName())] = $taxClass->getId();
            }
        }
        return $this;
    }

    /**
     * Creates new tax class.
     *
     * @param string $taxClassName
     * @param AbstractType $productTypeModel
     * @return integer
     */
    protected function createTaxClass($taxClassName, AbstractType $productTypeModel)
    {
        /** @var ClassModelFactory $taxClass */
        $taxClass = $this->classModelFactory->create();
        $taxClass->setClassType(ClassModel::TAX_CLASS_TYPE_PRODUCT);
        $taxClass->setClassName($taxClassName);
        $taxClass->save();

        $id = $taxClass->getId();

        $productTypeModel->addAttributeOption(self::ATRR_CODE, $id, $id);

        return $id;
    }

    /**
     * Instantiate instance of tax class.
     *
     * @param string $taxClassName
     * @param AbstractType $productTypeModel
     * @return object
     */
    public function upsertTaxClass($taxClassName, AbstractType $productTypeModel)
    {
        $normalizedTaxClassName = mb_strtolower($taxClassName);

        if ($normalizedTaxClassName === (string) self::CLASS_NONE_ID) {
            $normalizedTaxClassName = self::CLASS_NONE_NAME;
        }

        if (!isset($this->taxClasses[$normalizedTaxClassName])) {
            $this->taxClasses[$normalizedTaxClassName] = $normalizedTaxClassName === self::CLASS_NONE_NAME
                ? self::CLASS_NONE_ID
                : $this->createTaxClass($taxClassName, $productTypeModel);
        }
        if ($normalizedTaxClassName === self::CLASS_NONE_NAME) {
            // Add None option to tax_class_id options.
            $productTypeModel->addAttributeOption(self::ATRR_CODE, self::CLASS_NONE_ID, self::CLASS_NONE_ID);
        }

        return $this->taxClasses[$normalizedTaxClassName];
    }
}
