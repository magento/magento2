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

class LinksList
{
    /**
     * @var \Magento\Bundle\Api\Data\LinkDataBuilder
     */
    protected $linkBuilder;

    /**
     * @var Type
     */
    protected $type;

    /**
     * @param \Magento\Bundle\Api\Data\LinkDataBuilder $linkBuilder
     * @param Type $type
     */
    public function __construct(
        \Magento\Bundle\Api\Data\LinkDataBuilder $linkBuilder,
        \Magento\Bundle\Model\Product\Type $type
    ) {
        $this->linkBuilder = $linkBuilder;
        $this->type = $type;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param int $optionId
     * @return \Magento\Bundle\Api\Data\LinkInterface[]
     */
    public function getItems(\Magento\Catalog\Api\Data\ProductInterface $product, $optionId)
    {
        $selectionCollection = $this->type->getSelectionsCollection([$optionId], $product);

        $productLinks = [];
        /** @var \Magento\Catalog\Model\Product $selection */
        foreach ($selectionCollection as $selection) {
            $selectionPriceType = $product->getPriceType() ? $selection->getSelectionPriceType() : null;
            $selectionPrice = $product->getPriceType() ? $selection->getSelectionPriceValue() : null;

            $productLinks[] = $this->linkBuilder->populateWithArray($selection->getData())
                ->setIsDefault($selection->getIsDefault())
                ->setQty($selection->getSelectionQty())
                ->setIsDefined($selection->getSelectionCanChangeQty())
                ->setPrice($selectionPrice)
                ->setPriceType($selectionPriceType)
                ->create();
        }
        return $productLinks;
    }
}
