<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model;

use Magento\Catalog\Api\Data\ProductOptionInterface;
use Magento\Catalog\Model\ProductOptionProcessorInterface;
use Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface;
use Magento\ConfigurableProduct\Model\Quote\Item\ConfigurableItemOptionValueFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;

class ProductOptionProcessor implements ProductOptionProcessorInterface
{
    /**
     * @var DataObjectFactory
     */
    protected $objectFactory;

    /**
     * @var ConfigurableItemOptionValueFactory
     */
    protected $itemOptionValueFactory;

    /**
     * @param DataObjectFactory $objectFactory
     * @param ConfigurableItemOptionValueFactory $itemOptionValueFactory
     */
    public function __construct(
        DataObjectFactory $objectFactory,
        ConfigurableItemOptionValueFactory $itemOptionValueFactory
    ) {
        $this->objectFactory = $objectFactory;
        $this->itemOptionValueFactory = $itemOptionValueFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToBuyRequest(ProductOptionInterface $productOption)
    {
        /** @var DataObject $request */
        $request = $this->objectFactory->create();

        $options = $this->getConfigurableItemOptions($productOption);
        if (!empty($options)) {
            $requestData = [];
            foreach ($options as $option) {
                /** @var ConfigurableItemOptionValueInterface $option */
                $requestData['super_attribute'][$option->getOptionId()] = $option->getOptionValue();
            }
            $request->addData($requestData);
        }

        return $request;
    }

    /**
     * Retrieve configurable item options
     *
     * @param ProductOptionInterface $productOption
     * @return array
     */
    protected function getConfigurableItemOptions(ProductOptionInterface $productOption)
    {
        if ($productOption
            && $productOption->getExtensionAttributes()
            && $productOption->getExtensionAttributes()->getConfigurableItemOptions()
        ) {
            return $productOption->getExtensionAttributes()
                ->getConfigurableItemOptions();
        }
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToProductOption(DataObject $request)
    {
        $superAttribute = $request->getSuperAttribute();
        if (!empty($superAttribute) && is_array($superAttribute)) {
            $data = [];
            foreach ($superAttribute as $optionId => $optionValue) {
                /** @var ConfigurableItemOptionValueInterface $option */
                $option = $this->itemOptionValueFactory->create();
                $option->setOptionId($optionId);
                $option->setOptionValue($optionValue);
                $data[] = $option;
            }

            return ['configurable_item_options' => $data];
        }

        return [];
    }
}
