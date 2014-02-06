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

/**
 * Wrapper for \Magento\Sales\Model\Quote sales model
 */
namespace Magento\Payment\Model\Cart\SalesModel;

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
        $this->_address = $this->_salesModel->getIsVirtual() ?
            $this->_salesModel->getBillingAddress() : $this->_salesModel->getShippingAddress();
    }

    /**
     * Get all items from shopping sales model
     *
     * @return array
     */
    public function getAllItems()
    {
        $resultItems = array();

        foreach ($this->_salesModel->getAllItems() as $item) {
            $resultItems[] = new \Magento\Object(array(
                'parent_item'   => $item->getParentItem(),
                'name'          => $item->getName(),
                'qty'           => (int)$item->getTotalQty(),
                'price'         => $item->isNominal() ? 0 : (float)$item->getBaseCalculationPrice(),
                'original_item' => $item
            ));
        }

        return $resultItems;
    }

    /**
     * @return float|null
     */
    public function getBaseSubtotal()
    {
        return $this->_salesModel->getBaseSubtotal();
    }

    /**
     * @return float|null
     */
    public function getBaseTaxAmount()
    {
        return $this->_address->getBaseTaxAmount();
    }

    /**
     * @return float|null
     */
    public function getBaseShippingAmount()
    {
        return $this->_address->getBaseShippingAmount();
    }

    /**
     * @return float|null
     */
    public function getBaseDiscountAmount()
    {
        return $this->_address->getBaseDiscountAmount();
    }

    /**
     * Wrapper for \Magento\Object getDataUsingMethod method
     *
     * @param string $key
     * @param mixed $args
     * @return mixed
     */
    public function getDataUsingMethod($key, $args = null)
    {
        return $this->_salesModel->getDataUsingMethod($key, $args);
    }

    /**
     * Return object that contains tax related fields
     *
     * @return \Magento\Sales\Model\Quote\Address
     */
    public function getTaxContainer()
    {
        return $this->_salesModel->getIsVirtual()
            ? $this->_salesModel->getBillingAddress() : $this->_salesModel->getShippingAddress();
    }
}
