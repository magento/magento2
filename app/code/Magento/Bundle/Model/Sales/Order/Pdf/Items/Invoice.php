<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Sales\Order\Pdf\Items;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Tax\Helper\Data;

/**
 * Order invoice pdf default items renderer
 */
class Invoice extends AbstractItems
{
    /**
     * @var StringUtils
     */
    protected $string;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param Data $taxData
     * @param Filesystem $filesystem
     * @param FilterManager $filterManager
     * @param StringUtils $coreString
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
        StringUtils $coreString,
        Json $serializer,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->string = $coreString;
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
     * Draw bundle product item line
     *
     * @return void
     */
    public function draw()
    {
        $draw = $this->drawChildrenItems();
        $draw = $this->drawCustomOptions($draw);

        $page = $this->getPdf()->drawLineBlocks($this->getPage(), $draw, ['table_header' => true]);

        $this->setPage($page);
    }

    /**
     * Draw bundle product children items
     *
     * @return array
     */
    private function drawChildrenItems(): array
    {
        $this->_setFontRegular();

        $prevOptionId = '';
        $drawItems = [];
        $optionId = 0;
        $lines = [];
        foreach ($this->getChildren($this->getItem()) as $childItem) {
            $index = array_key_last($lines) !== null ? array_key_last($lines) + 1 : 0;
            $attributes = $this->getSelectionAttributes($childItem);
            if (is_array($attributes)) {
                $optionId = $attributes['option_id'];
            }

            if (!isset($drawItems[$optionId])) {
                $drawItems[$optionId] = ['lines' => [], 'height' => 15];
            }

            if ($childItem->getOrderItem()->getParentItem() && $prevOptionId != $attributes['option_id']) {
                $lines[$index][] = [
                    'font' => 'italic',
                    'text' => $this->string->split($attributes['option_label'], 45, true, true),
                    'feed' => 35,
                ];

                $index++;
                $prevOptionId = $attributes['option_id'];
            }

            /* in case Product name is longer than 80 chars - it is written in a few lines */
            if ($childItem->getOrderItem()->getParentItem()) {
                $feed = 40;
                $name = $this->getValueHtml($childItem);
            } else {
                $feed = 35;
                $name = $childItem->getName();
            }
            $lines[$index][] = ['text' => $this->string->split($name, 35, true, true), 'feed' => $feed];

            $lines = $this->drawSkus($childItem, $lines);

            $lines = $this->drawPrices($childItem, $lines);
        }
        $drawItems[$optionId]['lines'] = $lines;

        return $drawItems;
    }

    /**
     * Draw sku parts
     *
     * @param DataObject $childItem
     * @param array $lines
     * @return array
     */
    private function drawSkus(DataObject $childItem, array $lines): array
    {
        $index = array_key_last($lines);
        if (!$childItem->getOrderItem()->getParentItem()) {
            $text = [];
            foreach ($this->string->split($this->getItem()->getSku(), 17) as $part) {
                $text[] = $part;
            }
            $lines[$index][] = ['text' => $text, 'feed' => 255];
        }

        return $lines;
    }

    /**
     * Draw prices for bundle product children items
     *
     * @param DataObject $childItem
     * @param array $lines
     * @return array
     */
    private function drawPrices(DataObject $childItem, array $lines): array
    {
        $index = array_key_last($lines);
        if ($this->canShowPriceInfo($childItem)) {
            $lines[$index][] = ['text' => $childItem->getQty() * 1, 'feed' => 435, 'align' => 'right'];

            $tax = $this->getOrder()->formatPriceTxt($childItem->getTaxAmount());
            $lines[$index][] = ['text' => $tax, 'feed' => 495, 'font' => 'bold', 'align' => 'right'];

            $item = $this->getItem();
            $this->_item = $childItem;
            $feedPrice = 380;
            $feedSubtotal = $feedPrice + 185;
            foreach ($this->getItemPricesForDisplay() as $priceData) {
                if (isset($priceData['label'])) {
                    // draw Price label
                    $lines[$index][] = ['text' => $priceData['label'], 'feed' => $feedPrice, 'align' => 'right'];
                    // draw Subtotal label
                    $lines[$index][] = ['text' => $priceData['label'], 'feed' => $feedSubtotal, 'align' => 'right'];
                    $index++;
                }
                // draw Price
                $lines[$index][] = [
                    'text' => $priceData['price'],
                    'feed' => $feedPrice,
                    'font' => 'bold',
                    'align' => 'right',
                ];
                // draw Subtotal
                $lines[$index][] = [
                    'text' => $priceData['subtotal'],
                    'feed' => $feedSubtotal,
                    'font' => 'bold',
                    'align' => 'right',
                ];
                $index++;
            }
            $this->_item = $item;
        }

        return $lines;
    }

    /**
     * Draw bundle product custom options
     *
     * @param array $draw
     * @return array
     */
    private function drawCustomOptions(array $draw): array
    {
        $options = $this->getItem()->getOrderItem()->getProductOptions();
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
                    'feed' => 35,
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

                    $lines[][] = ['text' => $text, 'feed' => 40];
                }

                $draw[] = ['lines' => $lines, 'height' => 15];
            }
        }

        return $draw;
    }
}
