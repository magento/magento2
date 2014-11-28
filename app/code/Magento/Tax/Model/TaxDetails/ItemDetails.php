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
namespace Magento\Tax\Model\TaxDetails;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;

/**
 * @codeCoverageIgnore
 */
class ItemDetails extends AbstractExtensibleModel implements TaxDetailsItemInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxPercent()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_TAX_PERCENT);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceInclTax()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_PRICE_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function getRowTotal()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_ROW_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function getRowTotalInclTax()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_ROW_TOTAL_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function getRowTax()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_ROW_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxableAmount()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_TAXABLE_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getDiscountAmount()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_DISCOUNT_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getDiscountTaxCompensationAmount()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getAppliedTaxes()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_APPLIED_TAXES);
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociatedItemCode()
    {
        return $this->getData(TaxDetailsItemInterface::KEY_ASSOCIATED_ITEM_CODE);
    }
}
