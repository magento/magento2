<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product;

class OptionList
{
    /**
     * @var \Magento\Bundle\Api\Data\OptionInterfaceFactory
     */
    protected $optionFactory;

    /**
     * @var Type
     */
    protected $type;

    /**
     * @var LinksList
     */
    protected $linkList;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @param Type $type
     * @param \Magento\Bundle\Api\Data\OptionInterfaceFactory $optionFactory
     * @param LinksList $linkList
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        \Magento\Bundle\Model\Product\Type $type,
        \Magento\Bundle\Api\Data\OptionInterfaceFactory $optionFactory,
        \Magento\Bundle\Model\Product\LinksList $linkList,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->type = $type;
        $this->optionFactory = $optionFactory;
        $this->linkList = $linkList;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Bundle\Api\Data\OptionInterface[]
     */
    public function getItems(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $optionCollection = $this->type->getOptionsCollection($product);
        $this->extensionAttributesJoinProcessor->process($optionCollection);
        $optionList = [];
        /** @var \Magento\Bundle\Model\Option $option */
        foreach ($optionCollection as $option) {
            $productLinks = $this->linkList->getItems($product, $option->getOptionId());
            /** @var \Magento\Bundle\Api\Data\OptionInterface $optionDataObject */
            $optionDataObject = $this->optionFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $optionDataObject,
                $option->getData(),
                \Magento\Bundle\Api\Data\OptionInterface::class
            );
            $optionDataObject->setOptionId($option->getOptionId())
                ->setTitle($option->getTitle() === null ? $option->getDefaultTitle() : $option->getTitle())
                ->setDefaultTitle($option->getDefaultTitle())
                ->setSku($product->getSku())
                ->setProductLinks($productLinks);
            $optionList[] = $optionDataObject;
        }
        return $optionList;
    }
}
