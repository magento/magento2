<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Cart\Totals;

use Exception;
use Magento\Catalog\Helper\Product\Configuration;
use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\TotalsItemInterface;
use Magento\Quote\Api\Data\TotalsItemInterfaceFactory;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use RuntimeException;

/**
 * Cart item totals converter.
 */
class ItemConverter
{
    /**
     * @var ConfigurationPool
     */
    private $configurationPool;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var TotalsItemInterfaceFactory
     */
    private $totalsItemFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * Constructs a totals item converter object.
     *
     * @param ConfigurationPool $configurationPool
     * @param EventManager $eventManager
     * @param TotalsItemInterfaceFactory $totalsItemFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param Json $serializer
     * @throws RuntimeException
     */
    public function __construct(
        ConfigurationPool $configurationPool,
        EventManager $eventManager,
        TotalsItemInterfaceFactory $totalsItemFactory,
        DataObjectHelper $dataObjectHelper,
        Json $serializer
    ) {
        $this->configurationPool = $configurationPool;
        $this->eventManager = $eventManager;
        $this->totalsItemFactory = $totalsItemFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->serializer = $serializer;
    }

    /**
     * Converts a specified rate model to a shipping method data object.
     *
     * @param QuoteItem $item
     *
     * @return TotalsItemInterface
     * @throws Exception
     */
    public function modelToDataObject($item)
    {
        $this->eventManager->dispatch('items_additional_data', ['item' => $item]);
        $items = $item->toArray();
        $items['options'] = $this->getFormattedOptionValue($item);
        unset($items[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]);

        $itemsData = $this->totalsItemFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $itemsData,
            $items,
            TotalsItemInterface::class
        );
        return $itemsData;
    }

    /**
     * Retrieve formatted item options view
     *
     * @param CartItemInterface $item
     *
     * @return string
     */
    private function getFormattedOptionValue($item)
    {
        $optionsData = [];

        /* @var Configuration $helper */
        $helper = $this->configurationPool->getByProductType('default');

        $options = $this->configurationPool->getByProductType($item->getProductType())->getOptions($item);
        foreach ($options as $index => $optionValue) {
            $params = [
                'max_length' => 55,
                'cut_replacer' => ' <a href="#" class="dots tooltip toggle" onclick="return false">...</a>'
            ];
            $option = $helper->getFormattedOptionValue($optionValue, $params);
            $optionsData[$index] = $option;
            $optionsData[$index]['label'] = $optionValue['label'];
        }
        return $this->serializer->serialize($optionsData);
    }
}
