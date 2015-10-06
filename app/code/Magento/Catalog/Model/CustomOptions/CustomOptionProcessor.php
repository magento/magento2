<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\CustomOptions;

use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item\CartItemProcessorInterface;
use Magento\Catalog\Api\Data\CustomOptionInterface;

class CustomOptionProcessor implements CartItemProcessorInterface
{
    /** @var DataObject\Factory  */
    protected $objectFactory;

    /** @var \Magento\Quote\Model\Quote\ProductOptionFactory  */
    protected $productOptionFactory;

    /** @var \Magento\Quote\Api\Data\ProductOptionExtensionFactory  */
    protected $extensionFactory;

    /** @var CustomOptionFactory  */
    protected $customOptionFactory;

    /** @var string  */
    protected $quotePath = '/custom_options/quote';

    /** @var \Magento\Catalog\Model\Webapi\Product\Option\Type\File\Processor */
    protected $fileProcessor;

    /** @var \Magento\Catalog\Model\Product\OptionFactory */
    protected $optionFactory;

    /**
     * @param DataObject\Factory $objectFactory
     * @param \Magento\Quote\Model\Quote\ProductOptionFactory $productOptionFactory
     * @param \Magento\Quote\Api\Data\ProductOptionExtensionFactory $extensionFactory
     * @param CustomOptionFactory $customOptionFactory
     * @param \Magento\Catalog\Model\Webapi\Product\Option\Type\File\Processor $fileProcessor
     * @param \Magento\Catalog\Model\Product\OptionFactory $optionFactory
     */
    public function __construct(
        \Magento\Framework\DataObject\Factory $objectFactory,
        \Magento\Quote\Model\Quote\ProductOptionFactory $productOptionFactory,
        \Magento\Quote\Api\Data\ProductOptionExtensionFactory $extensionFactory,
        \Magento\Catalog\Model\CustomOptions\CustomOptionFactory $customOptionFactory,
        \Magento\Catalog\Model\Webapi\Product\Option\Type\File\Processor $fileProcessor,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory
    ) {
        $this->objectFactory = $objectFactory;
        $this->productOptionFactory = $productOptionFactory;
        $this->extensionFactory = $extensionFactory;
        $this->customOptionFactory = $customOptionFactory;
        $this->fileProcessor = $fileProcessor;
        $this->optionFactory = $optionFactory;
    }

    /**
     * @inheritDoc
     */
    public function convertToBuyRequest(CartItemInterface $cartItem)
    {
        if ($cartItem->getProductOption()
            && $cartItem->getProductOption()->getExtensionAttributes()
            && $cartItem->getProductOption()->getExtensionAttributes()->getCustomOptions()) {
            $customOptions = $cartItem->getProductOption()->getExtensionAttributes()->getCustomOptions();
            if (!empty($customOptions) && is_array($customOptions)) {
                $requestData = [];
                foreach ($customOptions as $option) {
                    $requestData['options'][$option->getOptionId()] = $this->getOptionValue($option);
                }
                return $this->objectFactory->create($requestData);
            }
        }
        return null;
    }

    /**
     * @param CustomOptionInterface $option
     * @return string
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function getOptionValue (CustomOptionInterface $option)
    {
        $value = $option->getOptionValue();
        if ($value == 'file') {
            /** @var \Magento\Framework\Api\Data\ImageContentInterface $fileInfo */
            $imageContent = $option->getExtensionAttributes()
                ? $option->getExtensionAttributes()->getFileInfo()
                : null;
            if ($imageContent) {
                $productCustomOption = $this->optionFactory->create();
                $productCustomOption->load($option->getOptionId());
                $value = $this->fileProcessor->processFileContent(
                    $imageContent,
                    $productCustomOption,
                    $this->quotePath
                );
            }
        }
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function processOptions(CartItemInterface $cartItem)
    {
        $options = $this->getOptions($cartItem);
        if (!empty($options) && is_array($options)) {
            $this->updateOptionsValues($options);
            $productOption = $cartItem->getProductOption()
                ? $cartItem->getProductOption()
                : $this->productOptionFactory->create();

            /** @var  \Magento\Quote\Api\Data\ProductOptionExtensionInterface $extensibleAttribute */
            $extensibleAttribute = $productOption->getExtensionAttributes()
                ? $productOption->getExtensionAttributes()
                : $this->extensionFactory->create();

            $extensibleAttribute->setCustomOptions($options);
            $productOption->setExtensionAttributes($extensibleAttribute);
            $cartItem->setProductOption($productOption);
        }
        return $cartItem;
    }

    /**
     * Receive custom option from buy request
     *
     * @param CartItemInterface $cartItem
     * @return array
     */
    protected function getOptions(CartItemInterface $cartItem)
    {
        $buyRequest = !empty($cartItem->getOptionByCode('info_buyRequest'))
            ? unserialize($cartItem->getOptionByCode('info_buyRequest')->getValue())
            : null;
        return is_array($buyRequest) && isset($buyRequest['options'])
            ? $buyRequest['options']
            : [];
    }

    /**
     * Update options values
     *
     * @param array $options
     * @return null
     */
    protected function updateOptionsValues(array &$options)
    {
        foreach ($options as $optionId => &$optionValue) {
            /** @var \Magento\Catalog\Model\CustomOptions\CustomOption $option */
            $option = $this->customOptionFactory->create();
            $option->setOptionId($optionId);
            if (is_array($optionValue)) {
                $optionValue = implode(',', $optionValue);
            }
            $option->setOptionValue($optionValue);
            $optionValue = $option;
        }
    }
}
