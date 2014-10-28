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
namespace Magento\Sales\Block\Adminhtml\Items\Column;

use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Quote\Item\AbstractItem as QuoteItem;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\Creditmemo\Item as CreditmemoItem;

/**
 * Adminhtml sales order column renderer
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class DefaultColumn extends \Magento\Sales\Block\Adminhtml\Items\AbstractItems
{
    /**
     * Option factory
     *
     * @var \Magento\Catalog\Model\Product\OptionFactory
     */
    protected $_optionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Service\V1\StockItemService $stockItemService
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\Product\OptionFactory $optionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Service\V1\StockItemService $stockItemService,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory,
        array $data = array()
    ) {
        $this->_optionFactory = $optionFactory;
        parent::__construct($context, $stockItemService, $registry, $data);
    }

    /**
     * Get item
     *
     * @return Item|QuoteItem
     */
    public function getItem()
    {
        $item = $this->_getData('item');
        if ($item instanceof Item || $item instanceof QuoteItem) {
            return $item;
        } else {
            return $item->getOrderItem();
        }
    }

    /**
     * Get order options
     *
     * @return array
     */
    public function getOrderOptions()
    {
        $result = array();
        if ($options = $this->getItem()->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (!empty($options['attributes_info'])) {
                $result = array_merge($options['attributes_info'], $result);
            }
        }
        return $result;
    }

    /**
     * Return custom option html
     *
     * @param array $optionInfo
     * @return string
     */
    public function getCustomizedOptionValue($optionInfo)
    {
        // render customized option view
        $_default = $optionInfo['value'];
        if (isset($optionInfo['option_type'])) {
            try {
                $group = $this->_optionFactory->create()->groupFactory($optionInfo['option_type']);
                return $group->getCustomizedView($optionInfo);
            } catch (\Exception $e) {
                return $_default;
            }
        }
        return $_default;
    }

    /**
     * Get sku
     *
     * @return string
     */
    public function getSku()
    {
        return $this->getItem()->getSku();
    }


    /**
     * Calculate total amount for the item
     *
     * @param QuoteItem|Item|InvoiceItem|CreditmemoItem $item
     * @return mixed
     */
    public function getTotalAmount($item)
    {
        $totalAmount = $item->getRowTotal() - $item->getDiscountAmount();

        return $totalAmount;
    }

    /**
     * Calculate base total amount for the item
     *
     * @param QuoteItem|Item|InvoiceItem|CreditmemoItem $item
     * @return mixed
     */
    public function getBaseTotalAmount($item)
    {
        $baseTotalAmount =  $item->getBaseRowTotal() - $item->getBaseDiscountAmount();

        return $baseTotalAmount;
    }
}
