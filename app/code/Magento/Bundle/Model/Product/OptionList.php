<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product;

/**
 * Class \Magento\Bundle\Model\Product\OptionList
 *
 * @since 2.0.0
 */
class OptionList
{
    /**
     * @var \Magento\Bundle\Api\Data\OptionInterfaceFactory
     * @since 2.0.0
     */
    protected $optionFactory;

    /**
     * @var Type
     * @since 2.0.0
     */
    protected $type;

    /**
     * @var LinksList
     * @since 2.0.0
     */
    protected $linkList;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     * @since 2.0.0
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     * @since 2.0.0
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @param Type $type
     * @param \Magento\Bundle\Api\Data\OptionInterfaceFactory $optionFactory
     * @param LinksList $linkList
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @since 2.0.0
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
     * @since 2.0.0
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
