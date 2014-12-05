<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Bundle\Model\Product;

class OptionList
{
    /**
     * @var \Magento\Bundle\Api\Data\OptionDataBuilder
     */
    protected $optionBuilder;

    /**
     * @var Type
     */
    protected $type;

    /**
     * @var LinksList
     */
    protected $linkList;

    /**
     * @param Type $type
     * @param \Magento\Bundle\Api\Data\OptionDataBuilder $optionBuilder
     * @param LinksList $linkList
     */
    public function __construct(
        \Magento\Bundle\Model\Product\Type $type,
        \Magento\Bundle\Api\Data\OptionDataBuilder $optionBuilder,
        \Magento\Bundle\Model\Product\LinksList $linkList
    ) {
        $this->type = $type;
        $this->optionBuilder = $optionBuilder;
        $this->linkList = $linkList;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Bundle\Api\Data\OptionInterface[]
     */
    public function getItems(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $optionCollection = $this->type->getOptionsCollection($product);
        $optionList = [];
        /** @var \Magento\Bundle\Model\Option $option */
        foreach ($optionCollection as $option) {
            $productLinks = $this->linkList->getItems($product, $option->getOptionId());
            $this->optionBuilder->populateWithArray($option->getData())
                ->setOptionId($option->getOptionId())
                ->setTitle(is_null($option->getTitle()) ? $option->getDefaultTitle() : $option->getTitle())
                ->setSku($product->getSku())
                ->setProductLinks($productLinks);
            $optionList[] = $this->optionBuilder->create();
        }
        return $optionList;
    }
}
