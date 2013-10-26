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
 * Sales Order Shipment Pdf items renderer
 *
 * @category   Magento
 * @package    Magento_Bundle
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Bundle\Model\Sales\Order\Pdf\Items;

class Shipment extends \Magento\Bundle\Model\Sales\Order\Pdf\Items\AbstractItems
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
        $item   = $this->getItem();
        $pdf    = $this->getPdf();
        $page   = $this->getPage();

        $this->_setFontRegular();

        $shipItems = $this->getChilds($item);
        $items = array_merge(array($item->getOrderItem()), $item->getOrderItem()->getChildrenItems());

        $_prevOptionId = '';
        $drawItems = array();

        $stringHelper = $this->_coreString;
        foreach ($items as $_item) {
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

            if ($_item->getParentItem()) {
                if ($_prevOptionId != $attributes['option_id']) {
                    $line[0] = array(
                        'font'  => 'italic',
                        'text'  => $this->_coreString->strSplit($attributes['option_label'], 60, true, true),
                        'feed'  => 60
                    );

                    $drawItems[$optionId] = array(
                        'lines'  => array($line),
                        'height' => 15
                    );

                    $line = array();

                    $_prevOptionId = $attributes['option_id'];
                }
            }

            if (($this->isShipmentSeparately() && $_item->getParentItem())
                || (!$this->isShipmentSeparately() && !$_item->getParentItem())
            ) {
                if (isset($shipItems[$_item->getId()])) {
                    $qty = $shipItems[$_item->getId()]->getQty()*1;
                } else if ($_item->getIsVirtual()) {
                    $qty = __('N/A');
                } else {
                    $qty = 0;
                }
            } else {
                $qty = '';
            }

            $line[] = array(
                'text'  => $qty,
                'feed'  => 35
            );

            // draw Name
            if ($_item->getParentItem()) {
                $feed = 65;
                $name = $this->getValueHtml($_item);
            } else {
                $feed = 60;
                $name = $_item->getName();
            }
            $text = array();
            foreach ($stringHelper->strSplit($name, 60, true, true) as $part) {
                $text[] = $part;
            }
            $line[] = array(
                'text'  => $text,
                'feed'  => $feed
            );

            // draw SKUs
            $text = array();
            foreach ($this->_coreString->strSplit($_item->getSku(), 25) as $part) {
                $text[] = $part;
            }
            $line[] = array(
                'text'  => $text,
                'feed'  => 440
            );

            $drawItems[$optionId]['lines'][] = $line;
        }

        // custom options
        $options = $item->getOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['options'])) {
                foreach ($options['options'] as $option) {
                    $lines = array();
                    $lines[][] = array(
                        'text'  => $stringHelper->strSplit(strip_tags($option['label']), 70, true, true),
                        'font'  => 'italic',
                        'feed'  => 60
                    );

                    if ($option['value']) {
                        $text = array();
                        $_printValue = isset($option['print_value'])
                            ? $option['print_value']
                            : strip_tags($option['value']);
                        $values = explode(', ', $_printValue);
                        foreach ($values as $value) {
                            foreach ($stringHelper->strSplit($value, 50, true, true) as $_value) {
                                $text[] = $_value;
                            }
                        }

                        $lines[][] = array(
                            'text'  => $text,
                            'feed'  => 65
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
