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
namespace Magento\Sales\Service\V1\Data;

use Magento\Framework\Service\Data\AbstractExtensibleObject as DataObject;

/**
 * Class CreditmemoItem
 */
class CreditmemoItem extends DataObject
{
    /**
     * int
     */
    const ENTITY_ID = 'entity_id';

    /**
     * int
     */
    const PARENT_ID = 'parent_id';

    /**
     * float
     */
    const BASE_PRICE = 'base_price';

    /**
     * float
     */
    const TAX_AMOUNT = 'tax_amount';

    /**
     * float
     */
    const BASE_ROW_TOTAL = 'base_row_total';

    /**
     * float
     */
    const DISCOUNT_AMOUNT = 'discount_amount';

    /**
     * float
     */
    const ROW_TOTAL = 'row_total';

    /**
     * float
     */
    const BASE_DISCOUNT_AMOUNT = 'base_discount_amount';

    /**
     * float
     */
    const PRICE_INCL_TAX = 'price_incl_tax';

    /**
     * float
     */
    const BASE_TAX_AMOUNT = 'base_tax_amount';

    /**
     * float
     */
    const BASE_PRICE_INCL_TAX = 'base_price_incl_tax';

    /**
     * float
     */
    const QTY = 'qty';

    /**
     * float
     */
    const BASE_COST = 'base_cost';

    /**
     * float
     */
    const PRICE = 'price';

    /**
     * float
     */
    const BASE_ROW_TOTAL_INCL_TAX = 'base_row_total_incl_tax';

    /**
     * float
     */
    const ROW_TOTAL_INCL_TAX = 'row_total_incl_tax';

    /**
     * int
     */
    const PRODUCT_ID = 'product_id';

    /**
     * int
     */
    const ORDER_ITEM_ID = 'order_item_id';

    /**
     * string
     */
    const ADDITIONAL_DATA = 'additional_data';

    /**
     * string
     */
    const DESCRIPTION = 'description';

    /**
     * string
     */
    const SKU = 'sku';

    /**
     * string
     */
    const NAME = 'name';

    /**
     * float
     */
    const HIDDEN_TAX_AMOUNT = 'hidden_tax_amount';

    /**
     * float
     */
    const BASE_HIDDEN_TAX_AMOUNT = 'base_hidden_tax_amount';

    /**
     * float
     */
    const WEEE_TAX_DISPOSITION = 'weee_tax_disposition';

    /**
     * float
     */
    const WEEE_TAX_ROW_DISPOSITION = 'weee_tax_row_disposition';

    /**
     * float
     */
    const BASE_WEEE_TAX_DISPOSITION = 'base_weee_tax_disposition';

    /**
     * float
     */
    const BASE_WEEE_TAX_ROW_DISPOSITION = 'base_weee_tax_row_disposition';

    /**
     * string
     */
    const WEEE_TAX_APPLIED = 'weee_tax_applied';

    /**
     * float
     */
    const BASE_WEEE_TAX_APPLIED_AMOUNT = 'base_weee_tax_applied_amount';

    /**
     * float
     */
    const BASE_WEEE_TAX_APPLIED_ROW_AMNT = 'base_weee_tax_applied_row_amnt';

    /**
     * float
     */
    const WEEE_TAX_APPLIED_AMOUNT = 'weee_tax_applied_amount';

    /**
     * float
     */
    const WEEE_TAX_APPLIED_ROW_AMOUNT = 'weee_tax_applied_row_amount';

    /**
     * Returns additional_data
     *
     * @return string
     */
    public function getAdditionalData()
    {
        return $this->_get(self::ADDITIONAL_DATA);
    }

    /**
     * Returns base_cost
     *
     * @return float
     */
    public function getBaseCost()
    {
        return $this->_get(self::BASE_COST);
    }

    /**
     * Returns base_discount_amount
     *
     * @return float
     */
    public function getBaseDiscountAmount()
    {
        return $this->_get(self::BASE_DISCOUNT_AMOUNT);
    }

    /**
     * Returns base_hidden_tax_amount
     *
     * @return float
     */
    public function getBaseHiddenTaxAmount()
    {
        return $this->_get(self::BASE_HIDDEN_TAX_AMOUNT);
    }

    /**
     * Returns base_price
     *
     * @return float
     */
    public function getBasePrice()
    {
        return $this->_get(self::BASE_PRICE);
    }

    /**
     * Returns base_price_incl_tax
     *
     * @return float
     */
    public function getBasePriceInclTax()
    {
        return $this->_get(self::BASE_PRICE_INCL_TAX);
    }

    /**
     * Returns base_row_total
     *
     * @return float
     */
    public function getBaseRowTotal()
    {
        return $this->_get(self::BASE_ROW_TOTAL);
    }

    /**
     * Returns base_row_total_incl_tax
     *
     * @return float
     */
    public function getBaseRowTotalInclTax()
    {
        return $this->_get(self::BASE_ROW_TOTAL_INCL_TAX);
    }

    /**
     * Returns base_tax_amount
     *
     * @return float
     */
    public function getBaseTaxAmount()
    {
        return $this->_get(self::BASE_TAX_AMOUNT);
    }

    /**
     * Returns base_weee_tax_applied_amount
     *
     * @return float
     */
    public function getBaseWeeeTaxAppliedAmount()
    {
        return $this->_get(self::BASE_WEEE_TAX_APPLIED_AMOUNT);
    }

    /**
     * Returns base_weee_tax_applied_row_amnt
     *
     * @return float
     */
    public function getBaseWeeeTaxAppliedRowAmnt()
    {
        return $this->_get(self::BASE_WEEE_TAX_APPLIED_ROW_AMNT);
    }

    /**
     * Returns base_weee_tax_disposition
     *
     * @return float
     */
    public function getBaseWeeeTaxDisposition()
    {
        return $this->_get(self::BASE_WEEE_TAX_DISPOSITION);
    }

    /**
     * Returns base_weee_tax_row_disposition
     *
     * @return float
     */
    public function getBaseWeeeTaxRowDisposition()
    {
        return $this->_get(self::BASE_WEEE_TAX_ROW_DISPOSITION);
    }

    /**
     * Returns description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_get(self::DESCRIPTION);
    }

    /**
     * Returns discount_amount
     *
     * @return float
     */
    public function getDiscountAmount()
    {
        return $this->_get(self::DISCOUNT_AMOUNT);
    }

    /**
     * Returns entity_id
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->_get(self::ENTITY_ID);
    }

    /**
     * Returns hidden_tax_amount
     *
     * @return float
     */
    public function getHiddenTaxAmount()
    {
        return $this->_get(self::HIDDEN_TAX_AMOUNT);
    }

    /**
     * Returns name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * Returns order_item_id
     *
     * @return int
     */
    public function getOrderItemId()
    {
        return $this->_get(self::ORDER_ITEM_ID);
    }

    /**
     * Returns parent_id
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->_get(self::PARENT_ID);
    }

    /**
     * Returns price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->_get(self::PRICE);
    }

    /**
     * Returns price_incl_tax
     *
     * @return float
     */
    public function getPriceInclTax()
    {
        return $this->_get(self::PRICE_INCL_TAX);
    }

    /**
     * Returns product_id
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->_get(self::PRODUCT_ID);
    }

    /**
     * Returns qty
     *
     * @return float
     */
    public function getQty()
    {
        return $this->_get(self::QTY);
    }

    /**
     * Returns row_total
     *
     * @return float
     */
    public function getRowTotal()
    {
        return $this->_get(self::ROW_TOTAL);
    }

    /**
     * Returns row_total_incl_tax
     *
     * @return float
     */
    public function getRowTotalInclTax()
    {
        return $this->_get(self::ROW_TOTAL_INCL_TAX);
    }

    /**
     * Returns sku
     *
     * @return string
     */
    public function getSku()
    {
        return $this->_get(self::SKU);
    }

    /**
     * Returns tax_amount
     *
     * @return float
     */
    public function getTaxAmount()
    {
        return $this->_get(self::TAX_AMOUNT);
    }

    /**
     * Returns weee_tax_applied
     *
     * @return string
     */
    public function getWeeeTaxApplied()
    {
        return $this->_get(self::WEEE_TAX_APPLIED);
    }

    /**
     * Returns weee_tax_applied_amount
     *
     * @return float
     */
    public function getWeeeTaxAppliedAmount()
    {
        return $this->_get(self::WEEE_TAX_APPLIED_AMOUNT);
    }

    /**
     * Returns weee_tax_applied_row_amount
     *
     * @return float
     */
    public function getWeeeTaxAppliedRowAmount()
    {
        return $this->_get(self::WEEE_TAX_APPLIED_ROW_AMOUNT);
    }

    /**
     * Returns weee_tax_disposition
     *
     * @return float
     */
    public function getWeeeTaxDisposition()
    {
        return $this->_get(self::WEEE_TAX_DISPOSITION);
    }

    /**
     * Returns weee_tax_row_disposition
     *
     * @return float
     */
    public function getWeeeTaxRowDisposition()
    {
        return $this->_get(self::WEEE_TAX_ROW_DISPOSITION);
    }
}
