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

namespace Magento\Tax\Model;

use Magento\Tax\Model\ClassModel as TaxClassModel;
use Magento\Tax\Model\ClassModelFactory as TaxClassFactory;
use Magento\Tax\Api\Data\TaxClassInterface as TaxClass;
use Magento\Tax\Api\Data\TaxClassDataBuilder;

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
