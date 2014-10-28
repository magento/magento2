<?php
/**
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
namespace Magento\GroupedProduct\Model\Product\CopyConstructor;

class Grouped implements \Magento\Catalog\Model\Product\CopyConstructorInterface
{
    /**
     * Retrieve collection grouped link
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Resource\Product\Link\Collection
     */
    protected function _getGroupedLinkCollection(\Magento\Catalog\Model\Product $product)
    {
        /** @var \Magento\Catalog\Model\Product\Link  $links */
        $links = $product->getLinkInstance();
        $links->setLinkTypeId(\Magento\GroupedProduct\Model\Resource\Product\Link::LINK_TYPE_GROUPED);

        $collection = $links->getLinkCollection();
        $collection->setProduct($product);
        $collection->addLinkTypeIdFilter();
        $collection->addProductIdFilter();
        $collection->joinAttributes();
        return $collection;
    }

    /**
     * Build product links
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Product $duplicate
     * @return void
     */
    public function build(\Magento\Catalog\Model\Product $product, \Magento\Catalog\Model\Product $duplicate)
    {
        if ($product->getTypeId() !== \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
            //do nothing if not grouped product
            return;
        }

        $data = array();
        $attributes = array();
        $link = $product->getLinkInstance();
        $link->setLinkTypeId(\Magento\GroupedProduct\Model\Resource\Product\Link::LINK_TYPE_GROUPED);
        foreach ($link->getAttributes() as $attribute) {
            if (isset($attribute['code'])) {
                $attributes[] = $attribute['code'];
            }
        }

        /** @var \Magento\Catalog\Model\Product\Link $link  */
        foreach ($this->_getGroupedLinkCollection($product) as $link) {
            $data[$link->getLinkedProductId()] = $link->toArray($attributes);
        }
        $duplicate->setGroupedLinkData($data);
    }
}
