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
namespace Magento\Payment\Model\Cart\SalesModel;

/**
 * Wrapper for \Magento\Sales\Model\Quote sales model
 */
class Quote implements \Magento\Payment\Model\Cart\SalesModel\SalesModelInterface
{
    /**
     * Sales quote model instance
     *
     * @var \Magento\Sales\Model\Quote
     */
    protected $_salesModel;

    /**
     * @var \Magento\Sales\Model\Quote\Address
     */
    protected $_address;

    /**
     * @param \Magento\Sales\Model\Quote $salesModel
     */
    public function __construct(\Magento\Sales\Model\Quote $salesModel)
    {
        $this->_salesModel = $salesModel;
        $this->_address = $this
            ->_salesModel
            ->getIsVirtual() ? $this
            ->_salesModel
            ->getBillingAddress() : $this
            ->_salesModel
            ->getShippingAddress();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllItems()
    {
        $resultItems = array();

        foreach ($this->_salesModel->getAllItems() as $item) {
            $resultItems[] = new \Magento\Framework\Object(
                array(
                    'parent_item' => $item->getParentItem(),
                    'name' => $item->getName(),
                    'qty' => (int)$item->getTotalQty(),
                    'price' => $item->isNominal() ? 0 : (double)$item->getBaseCalculationPrice(),
                    'original_item' => $item
                )
            );
        }

        return $resultItems;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseSubtotal()
    {
        return $this->_salesModel->getBaseSubtotal();
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTaxAmount()
    {
        return $this->_address->getBaseTaxAmount();
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseShippingAmount()
    {
        return $this->_address->getBaseShippingAmount();
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseDiscountAmount()
    {
        return $this->_address->getBaseDiscountAmount();
    }

    /**
     * {@inheritdoc}
     */
    public function getDataUsingMethod($key, $args = null)
    {
        return $this->_salesModel->getDataUsingMethod($key, $args);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxContainer()
    {
        return $this->_salesModel
            ->getIsVirtual() ? $this
            ->_salesModel
            ->getBillingAddress() : $this
            ->_salesModel
            ->getShippingAddress();
    }
}
