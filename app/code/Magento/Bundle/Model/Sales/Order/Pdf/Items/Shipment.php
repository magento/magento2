<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
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
 * Order shipment pdf items renderer
 */
class Shipment extends AbstractItems
{
    /**
     * @var StringUtils
     */
    protected $string;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Data $taxData
     * @param Filesystem $filesystem
     * @param FilterManager $filterManager
     * @param StringUtils $string
     * @param Json $serializer
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
        StringUtils $string,
        Json $serializer,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
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
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();

        $this->_setFontRegular();

        $shipItems = $this->getChildren($item);
        $items = array_merge([$item->getOrderItem()], $item->getOrderItem()->getChildrenItems());

        $prevOptionId = '';
        $drawItems = [];

        foreach ($items as $childItem) {
            $line = [];

            $attributes = $this->getSelectionAttributes($childItem);
            if (is_array($attributes)) {
                $optionId = $attributes['option_id'];
            } else {
                $optionId = 0;
            }

            if (!isset($drawItems[$optionId])) {
                $drawItems[$optionId] = ['lines' => [], 'height' => 20];
            }

            if ($childItem->getParentItem() && $prevOptionId != $attributes['option_id']) {
                $line[0] = [
                    'font' => 'italic',
                    'text' => $this->string->split($attributes['option_label'], 60, true, true),
                    'feed' => 100,
                ];

                $drawItems[$optionId] = ['lines' => [$line], 'height' => 20];

                $line = [];

                $prevOptionId = $attributes['option_id'];
            }

            if (($this->isShipmentSeparately() && $childItem->getParentItem()) ||
                (!$this->isShipmentSeparately() && !$childItem->getParentItem())
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

            $line[] = ['text' => $qty, 'feed' => 35];

            // draw Name
            if ($childItem->getParentItem()) {
                $feed = 110;
                $name = $this->getValueHtml($childItem);
            } else {
                $feed = 100;
                $name = $childItem->getName();
            }
            $text = [];
            foreach ($this->string->split($name, 60, true, true) as $part) {
                $text[] = $part;
            }
            $line[] = ['text' => $text, 'feed' => $feed];

            // draw SKUs
            $text = [];
            foreach ($this->string->split($childItem->getSku(), 25) as $part) {
                $text[] = $part;
            }
            $line[] = ['text' => $text, 'feed' => 565, 'align' => 'right'];

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
                        70,
                        true,
                        true
                    ),
                    'font' => 'italic',
                    'feed' => 110,
                ];

                if ($option['value']) {
                    $text = [];
                    $printValue = $option['print_value'] ?? $this->filterManager->stripTags($option['value']);
                    $printValue = str_replace(PHP_EOL, ', ', $printValue);
                    $values = explode(', ', $printValue);
                    foreach ($values as $value) {
                        foreach ($this->string->split($value, 50, true, true) as $subValue) {
                            $text[] = $subValue;
                        }
                    }

                    $lines[][] = ['text' => $text, 'feed' => 115];
                }

                $drawItems[] = ['lines' => $lines, 'height' => 20, 'shift' => 5];
            }
        }

        $page = $pdf->drawLineBlocks($page, $drawItems, ['table_header' => true]);
        $this->setPage($page);
    }
}
