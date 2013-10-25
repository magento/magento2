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
 * Sales Order Creditmemo Pdf default items renderer
 *
 * @category   Magento
 * @package    Magento_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Bundle\Model\Sales\Order\Pdf\Items;

class Creditmemo extends \Magento\Bundle\Model\Sales\Order\Pdf\Items\AbstractItems
{
    /**
     * Core string
     *
     * @var \Magento\Core\Helper\String
     */
    protected $_coreString = null;

    /**
     * @param \Magento\Core\Helper\String $coreString
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\App\Dir $coreDir
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\String $coreString,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\App\Dir $coreDir,
        \Magento\Data\Collection\Db $resourceCollection = null,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        array $data = array()
    ) {
        $this->_coreString = $coreString;
        parent::__construct($taxData, $context, $registry, $coreDir, $resource, $resourceCollection, $data);
    }

    /**
     * Draw item line
     *
     */
    public function draw()
    {
        $order  = $this->getOrder();
        $item   = $this->getItem();
        $pdf    = $this->getPdf();
        $page   = $this->getPage();

        $items = $this->getChilds($item);
        $_prevOptionId = '';
        $drawItems  = array();
        $leftBound  = 35;
        $rightBound = 565;

        $stringHelper = $this->_coreString;
        foreach ($items as $_item) {
            $x      = $leftBound;
            $line   = array();

            $attributes = $this->getSelectionAttributes($_item);
            if (is_array($attributes)) {
                $optionId   = $attributes['option_id'];
            }
            else {
                $optionId = 0;
            }

            if (!isset($drawItems[$optionId])) {
                $drawItems[$optionId] = array(
                    'lines'  => array(),
                    'height' => 15
                );
            }

            // draw selection attributes
            if ($_item->getOrderItem()->getParentItem()) {
                if ($_prevOptionId != $attributes['option_id']) {
                    $line[0] = array(
                        'font'  => 'italic',
                        'text'  => $this->_coreString->strSplit($attributes['option_label'], 38, true, true),
                        'feed'  => $x
                    );

                    $drawItems[$optionId] = array(
                        'lines'  => array($line),
                        'height' => 15
                    );

                    $line = array();
                    $_prevOptionId = $attributes['option_id'];
                }
            }

            // draw product titles
            if ($_item->getOrderItem()->getParentItem()) {
                $feed = $x + 5;
                $name = $this->getValueHtml($_item);
            } else {
                $feed = $x;
                $name = $_item->getName();
            }

            $line[] = array(
                'text'  => $this->_coreString->strSplit($name, 35, true, true),
                'feed'  => $feed
            );

            $x += 220;

            // draw SKUs
            if (!$_item->getOrderItem()->getParentItem()) {
                $text = array();
                foreach ($this->_coreString->strSplit($item->getSku(), 17) as $part) {
                    $text[] = $part;
                }
                $line[] = array(
                    'text'  => $text,
                    'feed'  => $x
                );
            }

            $x += 100;

            // draw prices
            if ($this->canShowPriceInfo($_item)) {
                // draw Total(ex)
                $text = $order->formatPriceTxt($_item->getRowTotal());
                $line[] = array(
                    'text'  => $text,
                    'feed'  => $x,
                    'font'  => 'bold',
                    'align' => 'right',
                    'width' => 50
                );
                $x += 50;

                // draw Discount
                $text = $order->formatPriceTxt(-$_item->getDiscountAmount());
                $line[] = array(
                    'text'  => $text,
                    'feed'  => $x,
                    'font'  => 'bold',
                    'align' => 'right',
                    'width' => 50
                );
                $x += 50;

                // draw QTY
                $text = $_item->getQty() * 1;
                $line[] = array(
                    'text'  => $_item->getQty()*1,
                    'feed'  => $x,
                    'font'  => 'bold',
                    'align' => 'center',
                    'width' => 30
                );
                $x += 30;

                // draw Tax
                $text = $order->formatPriceTxt($_item->getTaxAmount());
                $line[] = array(
                    'text'  => $text,
                    'feed'  => $x,
                    'font'  => 'bold',
                    'align' => 'right',
                    'width' => 45
                );
                $x += 45;

                // draw Total(inc)
                $text = $order->formatPriceTxt(
                    $_item->getRowTotal() + $_item->getTaxAmount() - $_item->getDiscountAmount()
                );
                $line[] = array(
                    'text'  => $text,
                    'feed'  => $rightBound,
                    'font'  => 'bold',
                    'align' => 'right',
                );
            }

            $drawItems[$optionId]['lines'][] = $line;

        }

        // custom options
        $options = $item->getOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['options'])) {
                foreach ($options['options'] as $option) {
                    $lines = array();
                    $lines[][] = array(
                        'text'  => $this->_coreString->strSplit(strip_tags($option['label']), 40, true, true),
                        'font'  => 'italic',
                        'feed'  => $leftBound
                    );

                    if ($option['value']) {
                        $text = array();
                        $_printValue = isset($option['print_value'])
                            ? $option['print_value']
                            : strip_tags($option['value']);
                        $values = explode(', ', $_printValue);
                        foreach ($values as $value) {
                            foreach ($this->_coreString->strSplit($value, 30, true, true) as $_value) {
                                $text[] = $_value;
                            }
                        }

                        $lines[][] = array(
                            'text'  => $text,
                            'feed'  => $leftBound + 5
                        );
                    }

                    $drawItems[] = array(
                        'lines'  => $lines,
                        'height' => 15
                    );
                }
            }
        }

        $page = $pdf->drawLineBlocks($page, $drawItems, array('table_header' => true));
        $this->setPage($page);
    }
}
