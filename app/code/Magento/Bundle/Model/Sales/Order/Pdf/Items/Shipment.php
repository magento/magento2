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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Bundle\Model\Sales\Order\Pdf\Items;

/**
 * Sales Order Shipment Pdf items renderer
 */
class Shipment extends \Magento\Bundle\Model\Sales\Order\Pdf\Items\AbstractItems
{
    /**
     * @var \Magento\Stdlib\String
     */
    protected $string;

    /**
     * @param \Magento\Model\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\App\Filesystem $filesystem
     * @param \Magento\Filter\FilterManager $filterManager
     * @param \Magento\Stdlib\String $string
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Model\Context $context,
        \Magento\Registry $registry,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\App\Filesystem $filesystem,
        \Magento\Filter\FilterManager $filterManager,
        \Magento\Stdlib\String $string,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->string = $string;
        parent::__construct(
            $context,
            $registry,
            $taxData,
            $filesystem,
            $filterManager,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Draw item line
     */
    public function draw()
    {
        $item   = $this->getItem();
        $pdf    = $this->getPdf();
        $page   = $this->getPage();

        $this->_setFontRegular();

        $shipItems = $this->getChilds($item);
        $items = array_merge(array($item->getOrderItem()), $item->getOrderItem()->getChildrenItems());

        $prevOptionId = '';
        $drawItems = array();

        foreach ($items as $childItem) {
            $line   = array();

            $attributes = $this->getSelectionAttributes($childItem);
            if (is_array($attributes)) {
                $optionId   = $attributes['option_id'];
            } else {
                $optionId = 0;
            }

            if (!isset($drawItems[$optionId])) {
                $drawItems[$optionId] = array(
                    'lines'  => array(),
                    'height' => 15
                );
            }

            if ($childItem->getParentItem()) {
                if ($prevOptionId != $attributes['option_id']) {
                    $line[0] = array(
                        'font'  => 'italic',
                        'text'  => $this->string->split($attributes['option_label'], 60, true, true),
                        'feed'  => 60
                    );

                    $drawItems[$optionId] = array(
                        'lines'  => array($line),
                        'height' => 15
                    );

                    $line = array();

                    $prevOptionId = $attributes['option_id'];
                }
            }

            if (($this->isShipmentSeparately() && $childItem->getParentItem())
                || (!$this->isShipmentSeparately() && !$childItem->getParentItem())
            ) {
                if (isset($shipItems[$childItem->getId()])) {
                    $qty = $shipItems[$childItem->getId()]->getQty() * 1;
                } elseif ($childItem->getIsVirtual()) {
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
            if ($childItem->getParentItem()) {
                $feed = 65;
                $name = $this->getValueHtml($childItem);
            } else {
                $feed = 60;
                $name = $childItem->getName();
            }
            $text = array();
            foreach ($this->string->split($name, 60, true, true) as $part) {
                $text[] = $part;
            }
            $line[] = array(
                'text'  => $text,
                'feed'  => $feed
            );

            // draw SKUs
            $text = array();
            foreach ($this->string->split($childItem->getSku(), 25) as $part) {
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
                        'text'  => $this->string->split(
                            $this->filterManager->stripTags($option['label']),
                            70,
                            true,
                            true
                        ),
                        'font'  => 'italic',
                        'feed'  => 60
                    );

                    if ($option['value']) {
                        $text = array();
                        $printValue = isset($option['print_value'])
                            ? $option['print_value']
                            : $this->filterManager->stripTags($option['value']);
                        $values = explode(', ', $printValue);
                        foreach ($values as $value) {
                            foreach ($this->string->split($value, 50, true, true) as $subValue) {
                                $text[] = $subValue;
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
