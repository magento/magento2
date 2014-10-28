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
 * Wrapper for \Magento\Sales\Model\Order sales model
 */
class Order implements \Magento\Payment\Model\Cart\SalesModel\SalesModelInterface
{
    /**
     * Sales order model instance
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_salesModel;

    /**
     * @param \Magento\Sales\Model\Order $salesModel
     */
    public function __construct(\Magento\Sales\Model\Order $salesModel)
    {
        $this->_salesModel = $salesModel;
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
                    'qty' => (int)$item->getQtyOrdered(),
                    'price' => (double)$item->getBasePrice(),
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
        return $this->_salesModel->getBaseTaxAmount();
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseShippingAmount()
    {
        return $this->_salesModel->getBaseShippingAmount();
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseDiscountAmount()
    {
        return $this->_salesModel->getBaseDiscountAmount();
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
        return $this->_salesModel;
    }
}
