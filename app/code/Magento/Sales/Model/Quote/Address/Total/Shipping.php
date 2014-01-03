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
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


namespace Magento\Sales\Model\Quote\Address\Total;

class Shipping extends \Magento\Sales\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * Set shipping code
     */
    public function __construct()
    {
        $this->setCode('shipping');
    }

    /**
     * Collect totals information about shipping
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @return  \Magento\Sales\Model\Quote\Address\Total\Shipping
     */
    public function collect(\Magento\Sales\Model\Quote\Address $address)
    {
        parent::collect($address);

        $address->setWeight(0);
        $address->setFreeMethodWeight(0);
        $this->_setAmount(0)->_setBaseAmount(0);

        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }

        $method             = $address->getShippingMethod();
        $freeAddress        = $address->getFreeShipping();
        $addressWeight      = $address->getWeight();
        $freeMethodWeight   = $address->getFreeMethodWeight();

        $addressQty = 0;

        foreach ($items as $item) {
            /**
             * Skip if this item is virtual
             */
            if ($item->getProduct()->isVirtual()) {
                continue;
            }

            /**
             * Children weight we calculate for parent
             */
            if ($item->getParentItem()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isShipSeparately()) {
                foreach ($item->getChildren() as $child) {
                    if ($child->getProduct()->isVirtual()) {
                        continue;
                    }
                    $addressQty += $child->getTotalQty();

                    if (!$item->getProduct()->getWeightType()) {
                        $itemWeight = $child->getWeight();
                        $itemQty    = $child->getTotalQty();
                        $rowWeight  = $itemWeight * $itemQty;
                        $addressWeight += $rowWeight;
                        if ($freeAddress || $child->getFreeShipping() === true) {
                            $rowWeight = 0;
                        } elseif (is_numeric($child->getFreeShipping())) {
                            $freeQty = $child->getFreeShipping();
                            if ($itemQty > $freeQty) {
                                $rowWeight = $itemWeight * ($itemQty - $freeQty);
                            } else {
                                $rowWeight = 0;
                            }
                        }
                        $freeMethodWeight += $rowWeight;
                        $item->setRowWeight($rowWeight);
                    }
                }
                if ($item->getProduct()->getWeightType()) {
                    $itemWeight = $item->getWeight();
                    $rowWeight  = $itemWeight*$item->getQty();
                    $addressWeight += $rowWeight;
                    if ($freeAddress || $item->getFreeShipping() === true) {
                        $rowWeight = 0;
                    } elseif (is_numeric($item->getFreeShipping())) {
                        $freeQty = $item->getFreeShipping();
                        if ($item->getQty() > $freeQty) {
                            $rowWeight = $itemWeight*($item->getQty()-$freeQty);
                        } else {
                            $rowWeight = 0;
                        }
                    }
                    $freeMethodWeight += $rowWeight;
                    $item->setRowWeight($rowWeight);
                }
            } else {
                if (!$item->getProduct()->isVirtual()) {
                    $addressQty += $item->getQty();
                }
                $itemWeight = $item->getWeight();
                $rowWeight  = $itemWeight * $item->getQty();
                $addressWeight += $rowWeight;
                if ($freeAddress || $item->getFreeShipping() === true) {
                    $rowWeight = 0;
                } elseif (is_numeric($item->getFreeShipping())) {
                    $freeQty = $item->getFreeShipping();
                    if ($item->getQty() > $freeQty) {
                        $rowWeight = $itemWeight * ($item->getQty() - $freeQty);
                    } else {
                        $rowWeight = 0;
                    }
                }
                $freeMethodWeight += $rowWeight;
                $item->setRowWeight($rowWeight);
            }
        }

        if (isset($addressQty)) {
            $address->setItemQty($addressQty);
        }

        $address->setWeight($addressWeight);
        $address->setFreeMethodWeight($freeMethodWeight);

        $address->collectShippingRates();

        $this->_setAmount(0)->_setBaseAmount(0);

        $method = $address->getShippingMethod();

        if ($method) {
            foreach ($address->getAllShippingRates() as $rate) {
                if ($rate->getCode() == $method) {
                    $amountPrice = $address->getQuote()->getStore()->convertPrice($rate->getPrice(), false);
                    $this->_setAmount($amountPrice);
                    $this->_setBaseAmount($rate->getPrice());
                    $shippingDescription = $rate->getCarrierTitle() . ' - ' . $rate->getMethodTitle();
                    $address->setShippingDescription(trim($shippingDescription, ' -'));
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * Add shipping totals information to address object
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @return  \Magento\Sales\Model\Quote\Address\Total\Shipping
     */
    public function fetch(\Magento\Sales\Model\Quote\Address $address)
    {
        $amount = $address->getShippingAmount();
        if ($amount != 0 || $address->getShippingDescription()) {
            $title = __('Shipping & Handling');
            if ($address->getShippingDescription()) {
                $title .= ' (' . $address->getShippingDescription() . ')';
            }
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => $title,
                'value' => $address->getShippingAmount()
            ));
        }
        return $this;
    }

    /**
     * Get Shipping label
     *
     * @return string
     */
    public function getLabel()
    {
        return __('Shipping');
    }
}
