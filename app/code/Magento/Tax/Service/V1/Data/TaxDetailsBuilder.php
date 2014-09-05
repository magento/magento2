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

namespace Magento\Tax\Service\V1\Data;

use Magento\Framework\Service\Data\AttributeValueBuilder;
use Magento\Framework\Service\Data\MetadataServiceInterface;

/**
 * Builder for the TaxDetails Data Object
 *
 * @method TaxDetails create()
 */
class TaxDetailsBuilder extends \Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder
{
    /**
     * Applied Tax data object builder
     *
     * @var \Magento\Tax\Service\V1\Data\TaxDetails\AppliedTaxBuilder
     */
    protected $appliedTaxBuilder;

    /**
     * Tax Details Item data object builder
     *
     * @var \Magento\Tax\Service\V1\Data\TaxDetails\ItemBuilder
     */
    protected $taxDetailsItemBuilder;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Service\Data\ObjectFactory $objectFactory
     * @param AttributeValueBuilder $valueBuilder
     * @param MetadataServiceInterface $metadataService
     * @param TaxDetails\AppliedTaxBuilder $appliedTaxBuilder
     * @param TaxDetails\ItemBuilder $taxDetailsItemBuilder
     */
    public function __construct(
        \Magento\Framework\Service\Data\ObjectFactory $objectFactory,
        AttributeValueBuilder $valueBuilder,
        MetadataServiceInterface $metadataService,
        \Magento\Tax\Service\V1\Data\TaxDetails\AppliedTaxBuilder $appliedTaxBuilder,
        \Magento\Tax\Service\V1\Data\TaxDetails\ItemBuilder $taxDetailsItemBuilder
    ) {
        parent::__construct($objectFactory, $valueBuilder, $metadataService);
        $this->appliedTaxBuilder = $appliedTaxBuilder;
        $this->taxDetailsItemBuilder = $taxDetailsItemBuilder;
    }

    /**
     * Convenience method that returns AppliedTaxBuilder
     *
     * @return TaxDetails\AppliedTaxBuilder
     */
    public function getAppliedTaxBuilder()
    {
        return $this->appliedTaxBuilder;
    }

    /**
     * Set subtotal
     *
     * @param float $subtotal
     * @return $this
     */
    public function setSubtotal($subtotal)
    {
        $this->_set(TaxDetails::KEY_SUBTOTAL, $subtotal);
        return $this;
    }

    /**
     * Set tax amount
     *
     * @param float $taxAmount
     * @return $this
     */
    public function setTaxAmount($taxAmount)
    {
        $this->_set(TaxDetails::KEY_TAX_AMOUNT, $taxAmount);
        return $this;
    }

    /**
     * Set discount amount
     *
     * @param float $discountAmount
     * @return $this
     */
    public function setDiscountCompensationAmount($discountAmount)
    {
        $this->_set(TaxDetails::KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT, $discountAmount);
        return $this;
    }

    /**
     * Set applied taxes
     *
     * @param \Magento\Tax\Service\V1\Data\TaxDetails\AppliedTax[] | null $appliedTaxes
     * @return $this
     */
    public function setAppliedTaxes($appliedTaxes)
    {
        $this->_set(TaxDetails::KEY_APPLIED_TAXES, $appliedTaxes);
        return $this;
    }

    /**
     * Set Tax Details items
     *
     * @param \Magento\Tax\Service\V1\Data\TaxDetails\Item[] | null $items
     * @return $this
     */
    public function setItems($items)
    {
        $this->_set(TaxDetails::KEY_ITEMS, $items);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function _setDataValues(array $data)
    {
        if (isset($data[TaxDetails::KEY_APPLIED_TAXES])) {
            $appliedTaxDataObjects = [];
            $appliedTaxes = $data[TaxDetails::KEY_APPLIED_TAXES];
            foreach ($appliedTaxes as $appliedTax) {
                $appliedTaxDataObjects[] = $this->appliedTaxBuilder
                    ->populateWithArray($appliedTax)->create();
            }
            $data[TaxDetails::KEY_APPLIED_TAXES] = $appliedTaxDataObjects;
        }

        if (isset($data[TaxDetails::KEY_ITEMS])) {
            $taxDetailItemDataObjects = [];
            $taxDetailItems = $data[TaxDetails::KEY_ITEMS];
            foreach ($taxDetailItems as $taxDetailItem) {
                $taxDetailItemDataObjects[] = $this->taxDetailsItemBuilder
                    ->populateWithArray($taxDetailItem)->create();
            }
            $data[TaxDetails::KEY_ITEMS] = $taxDetailItemDataObjects;
        }

        return parent::_setDataValues($data);
    }
}
