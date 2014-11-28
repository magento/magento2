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
namespace Magento\Tax\Model\Sales\Quote;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;

/**
 * @codeCoverageIgnore
 */
class ItemDetails extends AbstractExtensibleModel implements QuoteDetailsItemInterface
{

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getData(QuoteDetailsItemInterface::KEY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->getData(QuoteDetailsItemInterface::KEY_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxClassKey()
    {
        return $this->getData(QuoteDetailsItemInterface::KEY_TAX_CLASS_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitPrice()
    {
        return $this->getData(QuoteDetailsItemInterface::KEY_UNIT_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuantity()
    {
        return $this->getData(QuoteDetailsItemInterface::KEY_QUANTITY);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxIncluded()
    {
        return $this->getData(QuoteDetailsItemInterface::KEY_TAX_INCLUDED);
    }

    /**
     * {@inheritdoc}
     */
    public function getShortDescription()
    {
        return $this->getData(QuoteDetailsItemInterface::KEY_SHORT_DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function getDiscountAmount()
    {
        return $this->getData(QuoteDetailsItemInterface::KEY_DISCOUNT_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getParentCode()
    {
        return $this->getData(QuoteDetailsItemInterface::KEY_PARENT_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociatedItemCode()
    {
        return $this->getData(QuoteDetailsItemInterface::KEY_ASSOCIATED_ITEM_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxClassId()
    {
        return $this->getData(QuoteDetailsItemInterface::KEY_TAX_CLASS_ID);
    }
}
