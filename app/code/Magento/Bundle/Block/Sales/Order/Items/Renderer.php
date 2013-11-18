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
 * @package     Magento_Bundle
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Order item render block
 *
 * @category    Magento
 * @package     Magento_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Bundle\Block\Sales\Order\Items;

class Renderer extends \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer
{
    public function isShipmentSeparately($item = null)
    {
        if ($item) {
            if ($item->getOrderItem()) {
                $item = $item->getOrderItem();
            }
            if ($parentItem = $item->getParentItem()) {
                if ($options = $parentItem->getProductOptions()) {
                    if (isset($options['shipment_type'])
                        && $options['shipment_type'] == \Magento\Catalog\Model\Product\Type\AbstractType::SHIPMENT_SEPARATELY
                    ) {
                        return true;
                    } else {
                        return false;
                    }
                }
            } else {
                if ($options = $item->getProductOptions()) {
                    if (isset($options['shipment_type'])
                        && $options['shipment_type'] == \Magento\Catalog\Model\Product\Type\AbstractType::SHIPMENT_SEPARATELY
                    ) {
                        return false;
                    } else {
                        return true;
                    }
                }
            }
        }

        if ($options = $this->getOrderItem()->getProductOptions()) {
            if (isset($options['shipment_type'])
                && $options['shipment_type'] == \Magento\Catalog\Model\Product\Type\AbstractType::SHIPMENT_SEPARATELY
            ) {
                return true;
            }
        }
        return false;
    }

    public function isChildCalculated($item = null)
    {
        if ($item) {
            if ($item->getOrderItem()) {
                $item = $item->getOrderItem();
            }
            if ($parentItem = $item->getParentItem()) {
                if ($options = $parentItem->getProductOptions()) {
                    if (isset($options['product_calculations'])
                        && $options['product_calculations'] == \Magento\Catalog\Model\Product\Type\AbstractType::CALCULATE_CHILD
                    ) {
                        return true;
                    } else {
                        return false;
                    }
                }
            } else {
                if ($options = $item->getProductOptions()) {
                    if (isset($options['product_calculations'])
                        && $options['product_calculations'] == \Magento\Catalog\Model\Product\Type\AbstractType::CALCULATE_CHILD
                    ) {
                        return false;
                    } else {
                        return true;
                    }
                }
            }
        }

        if ($options = $this->getOrderItem()->getProductOptions()) {
            if (isset($options['product_calculations'])
                && $options['product_calculations'] == \Magento\Catalog\Model\Product\Type\AbstractType::CALCULATE_CHILD
            ) {
                return true;
            }
        }
        return false;
    }

    public function getSelectionAttributes($item) {
        if ($item instanceof \Magento\Sales\Model\Order\Item) {
            $options = $item->getProductOptions();
        } else {
            $options = $item->getOrderItem()->getProductOptions();
        }
        if (isset($options['bundle_selection_attributes'])) {
            return unserialize($options['bundle_selection_attributes']);
        }
        return null;
    }

    public function getValueHtml($item)
    {
        if ($attributes = $this->getSelectionAttributes($item)) {
            return sprintf('%d', $attributes['qty']) . ' x ' .
                $this->escapeHtml($item->getName()) .
                " " . $this->getOrder()->formatPrice($attributes['price']);
        } else {
            return $this->escapeHtml($item->getName());
        }
    }

    /**
     * Getting all available childs for Invoice, Shipmen or Creditmemo item
     *
     * @param \Magento\Object $item
     * @return array
     */
    public function getChilds($item)
    {
        $_itemsArray = array();

        if ($item instanceof \Magento\Sales\Model\Order\Invoice\Item) {
            $_items = $item->getInvoice()->getAllItems();
        } else if ($item instanceof \Magento\Sales\Model\Order\Shipment\Item) {
            $_items = $item->getShipment()->getAllItems();
        } else if ($item instanceof \Magento\Sales\Model\Order\Creditmemo\Item) {
            $_items = $item->getCreditmemo()->getAllItems();
        }

        if ($_items) {
            foreach ($_items as $_item) {
                if ($parentItem = $_item->getOrderItem()->getParentItem()) {
                    $_itemsArray[$parentItem->getId()][$_item->getOrderItemId()] = $_item;
                } else {
                    $_itemsArray[$_item->getOrderItem()->getId()][$_item->getOrderItemId()] = $_item;
                }
            }
        }

        if (isset($_itemsArray[$item->getOrderItem()->getId()])) {
            return $_itemsArray[$item->getOrderItem()->getId()];
        } else {
            return null;
        }
    }

    public function canShowPriceInfo($item)
    {
        if (($item->getOrderItem()->getParentItem() && $this->isChildCalculated())
                || (!$item->getOrderItem()->getParentItem() && !$this->isChildCalculated())) {
            return true;
        }
        return false;
    }
}
