<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Sales\Order\Pdf\Items;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Filesystem;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Tax\Helper\Data;

/**
 * Order creditmemo pdf default items renderer
 */
class Creditmemo extends AbstractItems
{
    /**
     * Core string
     *
     * @var StringUtils
     */
    protected $string;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Data $taxData
     * @param Filesystem $filesystem
     * @param FilterManager $filterManager
     * @param Json $serializer
     * @param StringUtils $string
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $taxData,
        Filesystem $filesystem,
        FilterManager $filterManager,
        Json $serializer,
        StringUtils $string,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->string = $string;
        parent::__construct(
            $context,
            $registry,
            $taxData,
            $filesystem,
            $filterManager,
            $serializer,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Draw item line
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function draw()
    {
        $order = $this->getOrder();
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();

        $items = $this->getChildren($item);
        $prevOptionId = '';
        $drawItems = [];
        $leftBound = 35;
        $rightBound = 565;

        foreach ($items as $childItem) {
            $x = $leftBound;
            $line = [];

            $attributes = $this->getSelectionAttributes($childItem);
            if (is_array($attributes)) {
                $optionId = $attributes['option_id'];
            } else {
                $optionId = 0;
            }

            if (!isset($drawItems[$optionId])) {
                $drawItems[$optionId] = ['lines' => [], 'height' => 15];
            }

            // draw selection attributes
            if ($childItem->getOrderItem()->getParentItem() && $prevOptionId != $attributes['option_id']) {
                $line[0] = [
                    'font' => 'italic',
                    'text' => $this->string->split($attributes['option_label'], 38, true, true),
                    'feed' => $x,
                ];

                $drawItems[$optionId] = ['lines' => [$line], 'height' => 15];

                $line = [];
                $prevOptionId = $attributes['option_id'];
            }

            // draw product titles
            if ($childItem->getOrderItem()->getParentItem()) {
                $feed = $x + 5;
                $name = $this->getValueHtml($childItem);
            } else {
                $feed = $x;
                $name = $childItem->getName();
            }

            $line[] = ['text' => $this->string->split($name, 35, true, true), 'feed' => $feed];

            $x += 220;

            // draw SKUs
            if (!$childItem->getOrderItem()->getParentItem()) {
                $text = [];
                foreach ($this->string->split($item->getSku(), 17) as $part) {
                    $text[] = $part;
                }
                $line[] = ['text' => $text, 'feed' => $x];
            }

            $x += 100;

            // draw prices
            if ($this->canShowPriceInfo($childItem)) {
                // draw Total(ex)
                $text = $order->formatPriceTxt($childItem->getRowTotal());
                $line[] = ['text' => $text, 'feed' => $x, 'font' => 'bold', 'align' => 'right', 'width' => 50];
                $x += 50;

                // draw Discount
                $text = $order->formatPriceTxt(-$childItem->getDiscountAmount());
                $line[] = ['text' => $text, 'feed' => $x, 'font' => 'bold', 'align' => 'right', 'width' => 50];
                $x += 50;

                // draw QTY
                $text = $childItem->getQty() * 1;
                $line[] = [
                    'text' => $text,
                    'feed' => $x,
                    'font' => 'bold',
                    'align' => 'center',
                    'width' => 30,
                ];
                $x += 30;

                // draw Tax
                $text = $order->formatPriceTxt($childItem->getTaxAmount());
                $line[] = ['text' => $text, 'feed' => $x, 'font' => 'bold', 'align' => 'right', 'width' => 45];
                $x += 45;

                // draw Total(inc)
                $text = $order->formatPriceTxt(
                    $childItem->getRowTotal() + $childItem->getTaxAmount() - $childItem->getDiscountAmount()
                );
                $line[] = ['text' => $text, 'feed' => $rightBound, 'font' => 'bold', 'align' => 'right'];
            }

            $drawItems[$optionId]['lines'][] = $line;
        }

        // custom options
        $options = $item->getOrderItem()->getProductOptions();
        if ($options && isset($options['options'])) {
            foreach ($options['options'] as $option) {
                $lines = [];
                $lines[][] = [
                    'text' => $this->string->split(
                        $this->filterManager->stripTags($option['label']),
                        40,
                        true,
                        true
                    ),
                    'font' => 'italic',
                    'feed' => $leftBound,
                ];

                if ($option['value']) {
                    $text = [];
                    $printValue = $option['print_value'] ?? $this->filterManager->stripTags($option['value']);
                    $values = explode(', ', $printValue);
                    foreach ($values as $value) {
                        foreach ($this->string->split($value, 30, true, true) as $subValue) {
                            $text[] = $subValue;
                        }
                    }

                    $lines[][] = ['text' => $text, 'feed' => $leftBound + 5];
                }

                $drawItems[] = ['lines' => $lines, 'height' => 15];
            }
        }

        $page = $pdf->drawLineBlocks($page, $drawItems, ['table_header' => true]);
        $this->setPage($page);
    }
}
