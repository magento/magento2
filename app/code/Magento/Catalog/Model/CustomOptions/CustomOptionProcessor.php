<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\CustomOptions;

use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item\CartItemProcessorInterface;
use Magento\Quote\Api\Data\ProductOptionExtensionFactory;
use Magento\Quote\Model\Quote\ProductOptionFactory;

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

    /** @var \Magento\Catalog\Model\Product\Option\UrlBuilder */
    private $urlBuilder;

    /**
     * Serializer interface instance.
     *
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @param DataObject\Factory $objectFactory
     * @param ProductOptionFactory $productOptionFactory
     * @param ProductOptionExtensionFactory $extensionFactory
     * @param CustomOptionFactory $customOptionFactory
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer [optional]
     */
    public function __construct(
        \Magento\Framework\DataObject\Factory $objectFactory,
        \Magento\Quote\Model\Quote\ProductOptionFactory $productOptionFactory,
        \Magento\Quote\Api\Data\ProductOptionExtensionFactory $extensionFactory,
        \Magento\Catalog\Model\CustomOptions\CustomOptionFactory $customOptionFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializer = null
    ) {
        $this->objectFactory = $objectFactory;
        $this->productOptionFactory = $productOptionFactory;
        $this->extensionFactory = $extensionFactory;
        $this->customOptionFactory = $customOptionFactory;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\SerializerInterface::class);
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
                    $requestData['options'][$option->getOptionId()] = $option->getOptionValue();
                }
                return $this->objectFactory->create($requestData);
            }
        }
        return null;
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
            ? $this->serializer->unserialize($cartItem->getOptionByCode('info_buyRequest')->getValue())
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
                $optionValue = $this->processFileOptionValue($optionValue);
                $optionValue = $this->processDateOptionValue($optionValue);
                $optionValue = implode(',', $optionValue);
            }
            $option->setOptionValue($optionValue);
            $optionValue = $option;
        }
    }

    /**
     * Returns option value with file built URL
     *
     * @param array $optionValue
     * @return array
     */
    private function processFileOptionValue(array $optionValue)
    {
        if (array_key_exists('url', $optionValue) &&
            array_key_exists('route', $optionValue['url']) &&
            array_key_exists('params', $optionValue['url'])
        ) {
            $optionValue['url'] = $this->getUrlBuilder()->getUrl(
                $optionValue['url']['route'],
                $optionValue['url']['params']
            );
        }
        return $optionValue;
    }

    /**
     * Returns date option value only with 'date_internal data
     *
     * @param array $optionValue
     * @return array
     */
    private function processDateOptionValue(array $optionValue)
    {
        if (array_key_exists('date_internal', $optionValue)
        ) {
            $closure = function ($key) {
                return $key === 'date_internal';
            };
            $optionValue = array_filter($optionValue, $closure, ARRAY_FILTER_USE_KEY);
        }
        return $optionValue;
    }

    /**
     * @return \Magento\Catalog\Model\Product\Option\UrlBuilder
     *
     * @deprecated
     */
    private function getUrlBuilder()
    {
        if ($this->urlBuilder === null) {
            $this->urlBuilder = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Catalog\Model\Product\Option\UrlBuilder::class);
        }
        return $this->urlBuilder;
    }
}
