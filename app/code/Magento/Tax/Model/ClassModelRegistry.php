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

use Magento\Tax\Model\ClassModelFactory as TaxClassModelFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Model\ClassModel as TaxClassModel;
use Magento\Tax\Service\V1\Data\TaxClass;

/**
 * Registry for the tax class models
 */
class ClassModelRegistry
{
    /**
     * Tax class model factory
     *
     * @var TaxClassModelFactory
     */
    private $taxClassModelFactory;

    /**
     * Tax class models
     *
     * @var TaxClassModel[]
     */
    private $taxClassRegistryById = [];

    /**
     * Initialize dependencies
     *
     * @param TaxClassModelFactory $taxClassModelFactory
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
            throw NoSuchEntityException::singleField(TaxClass::KEY_ID, $taxClassId);
        }
        $this->taxClassRegistryById[$taxClassModel->getId()] = $taxClassModel;
        return $taxClassModel;
    }

    /**
     * Remove an instance of the tax class model from the registry
     *
     * @param int $taxClassId
     * @return void
     */
    public function remove($taxClassId)
    {
        unset($this->taxClassRegistryById[$taxClassId]);
    }
}
