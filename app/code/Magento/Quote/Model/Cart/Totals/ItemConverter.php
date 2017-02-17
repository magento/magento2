<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Cart\Totals;

use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Cart item totals converter.
 *
 * @codeCoverageIgnore
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
     * @var \Magento\Quote\Api\Data\TotalsItemInterfaceFactory
     */
    private $totalsItemFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * Constructs a totals item converter object.
     *
     * @param ConfigurationPool $configurationPool
     * @param EventManager $eventManager
     * @param \Magento\Quote\Api\Data\TotalsItemInterfaceFactory $totalsItemFactory
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        ConfigurationPool $configurationPool,
        EventManager $eventManager,
        \Magento\Quote\Api\Data\TotalsItemInterfaceFactory $totalsItemFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->configurationPool = $configurationPool;
        $this->eventManager = $eventManager;
        $this->totalsItemFactory = $totalsItemFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * Converts a specified rate model to a shipping method data object.
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return array
     * @throws \Exception
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
            \Magento\Quote\Api\Data\TotalsItemInterface::class
        );
        return $itemsData;
    }

    /**
     * Retrieve formatted item options view
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return string
     */
    private function getFormattedOptionValue($item)
    {
        $optionsData = [];

        /* @var $helper \Magento\Catalog\Helper\Product\Configuration */
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
        return \Zend_Json::encode($optionsData);
    }
}
